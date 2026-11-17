<?php

namespace App\Models;

use App\Support\NairobiTime;
use Carbon\CarbonImmutable;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Something to turn up to: a meetup, fair, webinar or workshop.
 *
 * Events are short-lived by nature, so visibility is derived from the clock
 * rather than a status an editor has to remember to change: an event appears
 * while it is still ahead of us and drops off the homepage the moment it
 * starts. There is no detail page — the row links straight to the organiser.
 */
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'starts_at',
        'location',
        'banner_path',
        'banner_alt',
        'description',
        'url',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Saving without a date stamps the current time, so a record created in the
     * panel cannot sit invisible because nobody published it.
     */
    protected static function booted(): void
    {
        static::saving(function (self $event): void {
            $event->published_at ??= now();
        });
    }

    /**
     * Events still ahead of us, soonest first.
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    /**
     * The destination as it may be emitted into an href: http(s) only, so an
     * editor cannot inject a javascript: or data: URL from the panel.
     */
    public function safeUrl(): ?string
    {
        $url = trim((string) $this->url);

        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    /**
     * The start in the owner's timezone, for display.
     */
    public function startsAtNairobi(): CarbonImmutable
    {
        return NairobiTime::toNairobi($this->starts_at);
    }
}
