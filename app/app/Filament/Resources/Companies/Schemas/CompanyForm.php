<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(160),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->maxLength(180)
                    ->helperText('Leave blank to generate from the name.'),
                TextInput::make('website')
                    ->url()
                    ->maxLength(255),
                TextInput::make('logo_path')
                    ->maxLength(255)
                    ->helperText('Reserved for the imagery milestone; no uploads are enabled yet.'),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
