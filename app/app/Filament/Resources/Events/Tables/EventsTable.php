<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'asc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('D j M Y, H:i')
                    ->timezone('Africa/Nairobi')
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->placeholder('Online'),
                TextColumn::make('url')
                    ->label('Link')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Added')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('upcoming')
                    ->label('Upcoming only')
                    ->default()
                    ->query(fn (Builder $query): Builder => $query->where('starts_at', '>=', now())),
                Filter::make('past')
                    ->label('Past events')
                    ->query(fn (Builder $query): Builder => $query->where('starts_at', '<', now())),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No events yet')
            ->emptyStateDescription('Add a meetup, fair, webinar or workshop and it appears in the homepage sidebar until it starts.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateActions([]);
    }
}
