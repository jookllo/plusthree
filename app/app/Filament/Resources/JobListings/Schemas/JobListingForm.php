<?php

namespace App\Filament\Resources\JobListings\Schemas;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Enums\WorkMode;
use App\Models\JobListing;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JobListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(180),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->maxLength(200)
                    ->disabled(fn (?JobListing $record): bool => $record?->isPublished() ?? false)
                    ->helperText('Leave blank to generate from the title. Published slugs are locked until redirects exist.'),
                Select::make('employment_type')
                    ->options(EmploymentType::options())
                    ->required(),
                Select::make('work_mode')
                    ->options(WorkMode::options())
                    ->required(),
                TextInput::make('location_city')
                    ->maxLength(120),
                TextInput::make('location_country')
                    ->maxLength(80)
                    ->helperText('Where the role is based or who may apply. Kept out of the public search box by design.'),
                Textarea::make('description')
                    ->required()
                    ->rows(6)
                    ->columnSpanFull(),
                Textarea::make('requirements')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('application_url')
                    ->label('Application URL')
                    ->url()
                    ->maxLength(2048)
                    ->helperText('http or https only. Other schemes are rejected when the listing is displayed.'),
                TextInput::make('application_email')
                    ->email(),
                Select::make('status')
                    ->options(PublicationStatus::options())
                    ->default(PublicationStatus::Draft)
                    ->required()
                    ->helperText('Setting this to Published makes the listing live immediately.'),
                DatePicker::make('closing_at')
                    ->label('Closing date')
                    ->helperText('Applications close at the end of this day, Nairobi time. Blank means no deadline.'),
            ]);
    }
}
