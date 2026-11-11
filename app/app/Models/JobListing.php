<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Enums\WorkMode;
use App\Support\ArticleBody;
use App\Support\HasUniqueSlug;
use App\Support\Publishable;
use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    /**
     * How many days before the deadline a listing is flagged as urgent.
     */
    public const CLOSING_SOON_DAYS = 7;

    /**
     * Open roles a sector or country page needs before it is worth indexing.
     * Below this the page stays reachable but `noindex`.
     */
    public const LANDING_PAGE_MIN_ROLES = 3;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'description',
        'requirements',
        'featured_image_path',
        'featured_image_alt',
        'employment_type',
        'work_mode',
        'location_city',
        'location_country',
        'application_url',
        'application_email',
        'status',
        'published_at',
        'closing_at',
        'seo_title',
        'seo_description',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'work_mode' => WorkMode::class,
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'closing_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Vacancies the public may list: published, live, and not past the deadline.
     */
    public function scopeOpen(Builder $query): void
    {
        $query->published()->where(function (Builder $query): void {
            $query->whereNull('closing_at')->orWhere('closing_at', '>', now());
        });
    }

    /**
     * Records whose detail page may be served: published as normal, plus
     * archived roles so old links keep working instead of dead-ending. Drafts
     * and future-dated records stay private; only the signed preview shows them.
     */
    public function scopePubliclyReadable(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereIn('status', [PublicationStatus::Published, PublicationStatus::Archived]);
    }

    public function scopeRemote(Builder $query): void
    {
        $query->where('work_mode', WorkMode::Remote);
    }

    /**
     * Open roles whose deadline falls inside the urgency window.
     */
    public function scopeClosingSoon(Builder $query): void
    {
        $query->whereNotNull('closing_at')
            ->where('closing_at', '>', now())
            ->where('closing_at', '<=', now()->addDays(self::CLOSING_SOON_DAYS));
    }

    /**
     * How many open roles sit inside the urgency window right now.
     *
     * Drives the listing pages' urgency notice, which links to the
     * `closing=soon` view instead of lifting every near-deadline role above
     * the new ones. Counted within the page's own type so the Fellowships and
     * Other opportunities pages do not advertise standard jobs.
     */
    public static function closingSoonTotal(?EmploymentType $type = null): int
    {
        return static::query()
            ->open()
            ->closingSoon()
            ->when($type, fn (Builder $query) => $query->where('employment_type', $type))
            ->count();
    }

    public function scopeEmploymentType(Builder $query, EmploymentType $type): void
    {
        $query->where('employment_type', $type);
    }

    /**
     * Countries that currently have at least one open role, as landing pages.
     *
     * These are the board's only crawlable location targets; the search form
     * deliberately has no location field, and filtered URLs stay noindex.
     *
     * @return list<array{name: string, slug: string, total: int}>
     */
    public static function countryPages(): array
    {
        return static::query()
            ->open()
            ->whereNotNull('location_country')
            ->where('location_country', '<>', '')
            ->selectRaw('location_country as name, count(*) as total')
            ->groupBy('location_country')
            ->orderByDesc('total')
            ->orderBy('location_country')
            ->get()
            ->map(fn (object $row): array => [
                'name' => (string) $row->name,
                'slug' => Str::slug((string) $row->name),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * Job sectors (categories of kind `job`) with at least one open role.
     *
     * @return list<array{name: string, slug: string, total: int}>
     */
    public static function sectorPages(): array
    {
        return Category::query()
            ->where('kind', Category::KIND_JOB)
            ->withCount(['jobListings as open_total' => fn (Builder $query) => $query->open()])
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $category): bool => $category->open_total > 0)
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
                'total' => (int) $category->open_total,
            ])
            ->values()
            ->all();
    }

    public function isOpen(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        return $this->closing_at === null || $this->closing_at->greaterThan(now());
    }

    public function isClosed(): bool
    {
        return $this->published_at !== null
            && $this->closing_at !== null
            && $this->closing_at->lessThanOrEqualTo(now());
    }

    /**
     * Whole days left before the deadline, counted in Nairobi dates so a
     * listing closing tonight says "today" rather than "0.4 days".
     */
    public function daysUntilClosing(): ?int
    {
        if ($this->closing_at === null) {
            return null;
        }

        $today = now('Africa/Nairobi')->startOfDay();
        $closes = $this->closing_at->copy()->timezone('Africa/Nairobi')->startOfDay();

        return (int) round($today->diffInDays($closes));
    }

    /**
     * Whether the deadline is close enough to warn visitors about.
     */
    public function isClosingSoon(): bool
    {
        $days = $this->daysUntilClosing();

        return $this->isOpen()
            && $days !== null
            && $days >= 0
            && $days <= self::CLOSING_SOON_DAYS;
    }

    /**
     * Short urgency label for lists; null when the deadline is not near.
     */
    public function closingSoonLabel(): ?string
    {
        if (! $this->isClosingSoon()) {
            return null;
        }

        return match ($this->daysUntilClosing()) {
            0 => 'Closes today',
            1 => 'Closes tomorrow',
            default => 'Closing soon',
        };
    }

    /**
     * Search/social title: the override when set, otherwise the role title.
     */
    public function metaTitle(): string
    {
        return filled($this->seo_title) ? $this->seo_title : $this->title;
    }

    /**
     * Meta description: the override when set, otherwise the role's opening text.
     */
    public function metaDescription(): ?string
    {
        if (filled($this->seo_description)) {
            return $this->seo_description;
        }

        $text = ArticleBody::plainText($this->description);

        return $text === '' ? null : Str::limit($text, 155);
    }

    /**
     * The role description as sanitised, display-ready HTML.
     */
    public function renderedDescription(): HtmlString
    {
        return ArticleBody::render($this->description);
    }

    /**
     * The requirements as sanitised, display-ready HTML.
     */
    public function renderedRequirements(): HtmlString
    {
        return ArticleBody::render($this->requirements);
    }

    /**
     * Application destinations are limited to http/https; anything else is dropped.
     */
    public function safeApplicationUrl(): ?string
    {
        $url = trim((string) $this->application_url);

        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    public function canApply(): bool
    {
        return $this->isOpen()
            && ($this->safeApplicationUrl() !== null || filled($this->application_email));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
