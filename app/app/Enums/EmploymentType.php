<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Internship = 'internship';
    case Fellowship = 'fellowship';
    case Consultancy = 'consultancy';
    case Funding = 'funding';
    case Board = 'board';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Full-time',
            self::PartTime => 'Part-time',
            self::Contract => 'Contract',
            self::Internship => 'Internship',
            self::Fellowship => 'Fellowship',
            self::Consultancy => 'Consultancy',
            self::Funding => 'Funding',
            self::Board => 'Board',
            self::Other => 'Other opportunity',
        };
    }

    /**
     * Label for the sub-navigation on the Other opportunities page, where
     * "Other opportunity" beside "All" would read as a contradiction.
     */
    public function navLabel(): string
    {
        return $this === self::Other ? 'Other' : $this->label();
    }

    /**
     * Values accepted by the public ?type= filter, linked from the header menu.
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

    /**
     * The types that are not a standard job and share the "Other
     * opportunities" page. These are opportunity types, not categories:
     * categories describe sectors, and a grant call is not a sector.
     *
     * @return list<self>
     */
    public static function otherOpportunityTypes(): array
    {
        return [self::Consultancy, self::Funding, self::Board, self::Other];
    }

    /**
     * @return list<string>
     */
    public static function otherOpportunityValues(): array
    {
        return array_map(fn (self $case): string => $case->value, self::otherOpportunityTypes());
    }

    /**
     * The types a landing page covers. Fellowships stand alone; Other
     * groups consultancy, funding, board seats and catch-all opportunities.
     *
     * @return list<self>
     */
    public function landingTypes(): array
    {
        return $this === self::Other ? self::otherOpportunityTypes() : [$this];
    }
}
