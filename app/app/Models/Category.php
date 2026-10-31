<?php

namespace App\Models;

use App\Support\HasUniqueSlug;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasUniqueSlug;

    public const KIND_JOB = 'job';

    public const KIND_ARTICLE = 'article';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kind',
        'name',
        'slug',
    ];

    public function jobListings(): MorphToMany
    {
        return $this->morphedByMany(JobListing::class, 'categorizable');
    }

    public function articles(): MorphToMany
    {
        return $this->morphedByMany(Article::class, 'categorizable');
    }

    /**
     * Categories share one table, so a slug is only unique within its kind.
     *
     * @return array<string, mixed>
     */
    protected function slugScope(): array
    {
        return ['kind' => $this->kind];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
