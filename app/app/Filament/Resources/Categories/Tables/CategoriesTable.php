<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('kind')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Category::KIND_JOB => 'Job sector',
                        Category::KIND_ARTICLE => 'Article category',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Category::KIND_JOB => 'info',
                        Category::KIND_ARTICLE => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->options([
                        Category::KIND_JOB => 'Job sectors',
                        Category::KIND_ARTICLE => 'Article categories',
                    ]),
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
