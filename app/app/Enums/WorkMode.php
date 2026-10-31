<?php

namespace App\Enums;

enum WorkMode: string
{
    case OnSite = 'on_site';
    case Hybrid = 'hybrid';
    case Remote = 'remote';

    public function label(): string
    {
        return match ($this) {
            self::OnSite => 'On-site',
            self::Hybrid => 'Hybrid',
            self::Remote => 'Remote',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Value/label pairs for selects and filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $case): array => [$case->value, $case->label()], self::cases()),
            1,
            0
        );
    }
}
