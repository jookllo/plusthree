<?php

namespace App\Support;

use App\Enums\PublicationStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single definition of "publicly visible" for publishable content.
 *
 * Visibility is derived from data at query time, so a delayed scheduler can
 * never expose a future or archived record.
 */
trait Publishable
{
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PublicationStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Setting the status to published without a date stamps the current time,
     * so the public scope cannot hide a record the editor believes is live.
     */
    public static function bootPublishable(): void
    {
        static::saving(function (self $model): void {
            if ($model->status === PublicationStatus::Published && $model->published_at === null) {
                $model->published_at = now();
            }
        });
    }

    public function isPublished(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now());
    }

    public function publish(?CarbonInterface $at = null): void
    {
        $this->status = PublicationStatus::Published;
        $this->published_at ??= $at ?? now();
        $this->save();
    }

    public function unpublish(): void
    {
        $this->status = PublicationStatus::Draft;
        $this->save();
    }

    public function archive(): void
    {
        $this->status = PublicationStatus::Archived;
        $this->save();
    }
}
