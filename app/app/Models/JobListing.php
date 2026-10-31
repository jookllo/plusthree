<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Enums\WorkMode;
use App\Support\HasUniqueSlug;
use App\Support\Publishable;
use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'description',
        'requirements',
        'employment_type',
        'work_mode',
        'location_city',
        'location_country',
        'application_url',
        'application_email',
        'status',
        'published_at',
        'closing_at',
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

    public function scopeRemote(Builder $query): void
    {
        $query->where('work_mode', WorkMode::Remote);
    }

    public function scopeEmploymentType(Builder $query, EmploymentType $type): void
    {
        $query->where('employment_type', $type);
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
