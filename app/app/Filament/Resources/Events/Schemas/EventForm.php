<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
