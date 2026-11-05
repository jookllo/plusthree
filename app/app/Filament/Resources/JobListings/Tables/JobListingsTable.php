<?php

namespace App\Filament\Resources\JobListings\Tables;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Enums\WorkMode;
use App\Models\JobListing;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('company.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employment_type')
                    ->badge()
                    ->formatStateUsing(fn (EmploymentType $state): string => $state->label()),
                TextColumn::make('work_mode')
                    ->badge()
                    ->formatStateUsing(fn (WorkMode $state): string => $state->label()),
                TextColumn::make('location_country')
                    ->label('Country')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PublicationStatus $state): string => $state->label())
                    ->color(fn (PublicationStatus $state): string => match ($state) {
                        PublicationStatus::Published => 'success',
                        PublicationStatus::Draft => 'warning',
                        PublicationStatus::Archived => 'gray',
                    }),
                TextColumn::make('published_at')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi')
                    ->sortable(),
                TextColumn::make('closing_at')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('closing_soon')
                    ->label('Urgency')
                    ->badge()
                    ->state(fn (JobListing $record): ?string => $record->closingSoonLabel())
                    ->color('danger')
                    ->placeholder('—'),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublicationStatus::options()),
                SelectFilter::make('employment_type')
                    ->options(EmploymentType::options()),
                SelectFilter::make('work_mode')
                    ->options(WorkMode::options()),
                Filter::make('closing_soon')
                    ->label('Closing soon')
                    ->query(fn ($query) => $query->closingSoon()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
