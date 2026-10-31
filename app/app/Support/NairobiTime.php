<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;

/**
 * Explicit boundary conversion for the owner's timezone.
 *
 * Timestamps are persisted in UTC. Owner input is collected in Africa/Nairobi
 * and must pass through toUtc() before it reaches a model attribute; otherwise
 * a naive local string would be interpreted as UTC.
 */
final class NairobiTime
{
    public const TIMEZONE = 'Africa/Nairobi';

    public static function toUtc(DateTimeInterface|string $value): CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)->utc();
        }

        return CarbonImmutable::parse($value, self::TIMEZONE)->utc();
    }

    public static function toNairobi(DateTimeInterface $value): CarbonImmutable
    {
        return CarbonImmutable::instance($value)->setTimezone(self::TIMEZONE);
    }

    /**
     * A chosen calendar date becomes the last moment of that day in Nairobi,
     * which is what "closing date" means to an applicant.
     */
    public static function endOfDayUtc(DateTimeInterface|string $value): CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)
                ->setTimezone(self::TIMEZONE)
                ->endOfDay()
                ->utc();
        }

        return CarbonImmutable::parse($value, self::TIMEZONE)->endOfDay()->utc();
    }
}
