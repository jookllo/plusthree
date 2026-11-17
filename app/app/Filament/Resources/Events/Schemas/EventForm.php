<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(160)
                    ->helperText('How it appears on the homepage. Keep it short: the sidebar is narrow.'),
                DateTimePicker::make('starts_at')
                    ->label('Starts')
                    ->seconds(false)
                    ->required()
                    ->helperText('Nairobi time. The event leaves the homepage as soon as this passes, so there is nothing to tidy afterwards.'),
                TextInput::make('location')
                    ->maxLength(160)
                    ->helperText('City and country, or type Online for a virtual event.'),
                FileUpload::make('banner_path')
                    ->label('Banner')
                    ->image()
                    ->disk('public')
                    ->directory('events')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->imageAspectRatio('16:9')
                    ->automaticallyCropImagesToAspectRatio()
                    ->automaticallyResizeImagesToWidth('1200')
                    ->automaticallyResizeImagesToHeight('675')
                    ->automaticallyUpscaleImagesWhenResizing(false)
                    ->helperText('Optional. Cropped to 16:9 and resized to 1200×675. The soonest event shows its banner above its date; the other rows stay text only.')
                    ->columnSpanFull(),
                TextInput::make('banner_alt')
                    ->label('Banner alt text')
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => filled($get('banner_path')))
                    ->helperText('Describe the image for screen readers. Required when a banner is set.'),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(600)
                    ->helperText('One or two sentences. Shown under the title and trimmed to two lines.'),
                TextInput::make('url')
                    ->label('Link to go to')
                    ->required()
                    ->url()
                    ->maxLength(500)
                    ->helperText('Registration page, RSVP form or the organiser’s event page. Only http and https links are published.'),
            ]);
    }
}
