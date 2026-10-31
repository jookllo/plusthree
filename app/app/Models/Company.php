<?php

namespace App\Models;

use App\Support\HasUniqueSlug;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasUniqueSlug;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'website',
        'logo_path',
        'description',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
