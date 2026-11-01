<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\PublicationStatus;
use App\Models\Article;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
                FileUpload::make('featured_image_path')
                    ->label('Featured image')
                    ->image()
                    ->disk('public')
                    ->directory('articles')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->imageAspectRatio('16:9')
                    ->automaticallyCropImagesToAspectRatio()
                    ->automaticallyResizeImagesToWidth('1600')
                    ->automaticallyResizeImagesToHeight('900')
                    ->automaticallyUpscaleImagesWhenResizing(false)
                    ->helperText('Optional. Cropped to 16:9 and resized to 1600×900. Without an image the article uses one of 20 generated panels.')
                    ->columnSpanFull(),
                TextInput::make('featured_image_alt')
                    ->label('Image alt text')
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => filled($get('featured_image_path')))
                    ->helperText('Describe the image for screen readers. Required when an image is set.'),
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
