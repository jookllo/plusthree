<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\PublicationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
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
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
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
                TextColumn::make('updated_at')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublicationStatus::options()),
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
