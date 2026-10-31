<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(60),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->maxLength(80)
                    ->helperText('Leave blank to generate from the name.'),
            ]);
    }
}
