<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\PublicationStatus;
use App\Models\Article;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(200),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->maxLength(200)
                    ->disabled(fn (?Article $record): bool => $record?->isPublished() ?? false)
                    ->helperText('Leave blank to generate from the title. Published slugs are locked until redirects exist.'),
                Select::make('author_id')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Defaults to the signed-in editor when left blank.'),
                Textarea::make('excerpt')
                    ->rows(2)
                    ->maxLength(320)
                    ->helperText('Used on cards and as the meta description fallback.'),
                Textarea::make('body')
                    ->required()
                    ->rows(12)
                    ->columnSpanFull()
                    ->helperText('Plain text for now. Rich-text sanitisation arrives with the editor upgrade.'),
                Select::make('categories')
                    ->relationship(
                        'categories',
                        'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('kind', Category::KIND_ARTICLE),
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Select::make('status')
                    ->options(PublicationStatus::options())
                    ->default(PublicationStatus::Draft)
                    ->required()
                    ->helperText('Setting this to Published makes the article live immediately.'),
                TextInput::make('seo_title')
                    ->label('SEO title')
                    ->maxLength(255)
                    ->helperText('Falls back to the headline.'),
                Textarea::make('seo_description')
                    ->label('SEO description')
                    ->rows(2)
                    ->maxLength(320)
                    ->helperText('Falls back to the excerpt.'),
            ]);
    }
}
