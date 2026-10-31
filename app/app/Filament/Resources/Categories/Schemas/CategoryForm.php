<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kind')
                    ->options([
                        Category::KIND_JOB => 'Job sector',
                        Category::KIND_ARTICLE => 'Article category',
                    ])
                    ->required()
                    ->helperText('One taxonomy, two kinds. A slug only has to be unique within its kind.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(80),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->helperText('Leave blank to generate from the name.'),
            ]);
    }
}
