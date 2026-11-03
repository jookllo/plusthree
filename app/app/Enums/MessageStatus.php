<?php

namespace App\Enums;

enum MessageStatus: string
{
    case New = 'new';
    case Handled = 'handled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Handled => 'Handled',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
