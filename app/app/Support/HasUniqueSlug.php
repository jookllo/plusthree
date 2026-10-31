<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Generates a stable, unique slug from a model's title or name.
 *
 * Models may narrow uniqueness with slugScope(), e.g. categories are unique
 * per kind rather than globally.
 */
trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlug($model->slugSource());
            }
        });
    }

    protected function slugSource(): string
    {
        return (string) ($this->title ?? $this->name ?? '');
    }

    /**
     * Extra columns that scope slug uniqueness. Empty means global uniqueness.
     *
     * @return array<string, mixed>
     */
    protected function slugScope(): array
    {
        return [];
    }

    protected function uniqueSlug(string $source): string
    {
        $base = Str::slug($source) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        foreach ($this->slugScope() as $column => $value) {
            $query->where($column, $value);
        }

        return $query->exists();
    }
}
