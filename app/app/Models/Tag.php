<?php

namespace App\Models;

use App\Support\HasUniqueSlug;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, HasUniqueSlug;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    public function jobListings(): MorphToMany
    {
        return $this->morphedByMany(JobListing::class, 'taggable');
    }

    public function articles(): MorphToMany
    {
        return $this->morphedByMany(Article::class, 'taggable');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
