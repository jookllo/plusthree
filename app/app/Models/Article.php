<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\ArticleBody;
use App\Support\HasUniqueSlug;
use App\Support\Publishable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\HtmlString;

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
        $text = ArticleBody::plainText($this->body);
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * Articles to offer at the foot of this one, newest first: those sharing
     * a category, topped up with the most recent posts.
     *
     * Without the top-up the block is empty whenever an article has not been
     * filed under a topic — which is most of the production archive — and an
     * empty "Keep reading" is worse than none. The footer of a post is where a
     * reader either leaves or reads a second piece, so it always offers one.
     *
     * @return Collection<int, self>
     */
    public function relatedArticles(int $limit = 3): Collection
    {
        $picked = static::query()
            ->published()
            ->whereKeyNot($this->getKey())
            ->when(
                $this->categories->isNotEmpty(),
                fn (Builder $query) => $query->whereHas(
                    'categories',
                    fn (Builder $query) => $query->whereIn('categories.id', $this->categories->modelKeys()),
                ),
            )
            ->latest('published_at')
            ->limit($limit)
            ->get();

        if ($picked->count() < $limit) {
            $picked = $picked->concat(
                static::query()
                    ->published()
                    ->whereKeyNot($this->getKey())
                    ->whereNotIn($this->getKeyName(), $picked->modelKeys())
                    ->latest('published_at')
                    ->limit($limit - $picked->count())
                    ->get(),
            );
        }

        return $picked;
    }

    /**
     * The body as sanitised, display-ready HTML.
     */
    public function renderedBody(): HtmlString
    {
        return ArticleBody::render($this->body);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
