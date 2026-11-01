<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\HasUniqueSlug;
use App\Support\Publishable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_image_path',
        'featured_image_alt',
        'status',
        'published_at',
        'seo_title',
        'seo_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
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
     * Search/social title: the override when set, otherwise the headline.
     */
    public function metaTitle(): string
    {
        return filled($this->seo_title) ? $this->seo_title : $this->title;
    }

    /**
     * Meta description: the override when set, otherwise the excerpt.
     */
    public function metaDescription(): ?string
    {
        return filled($this->seo_description) ? $this->seo_description : $this->excerpt;
    }

    /**
     * Stable fallback art variant (1–20) for articles without a photograph.
     *
     * Derived from the slug so the same article always gets the same panel
     * on the article page, independent of list position or request.
     */
    public function artVariant(): int
    {
        $seed = $this->slug ?: (string) $this->getKey();

        return (int) (crc32($seed) % 20) + 1;
    }

    /**
     * Rough reading time for editorial display; derived, never stored.
     */
    public function readingTimeMinutes(): int
    {
        $text = trim(Str::of($this->body ?? '')->stripTags()->toString());
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));

        return max(1, (int) ceil($words / 200));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
