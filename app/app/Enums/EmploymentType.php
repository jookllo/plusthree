<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case Contract = 'contract';
    case Internship = 'internship';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Full-time',
            self::Contract => 'Contract',
            self::Internship => 'Internship',
        };
    }

    /**
     * Values that match the existing public filter links in home.blade.php.
     *
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
