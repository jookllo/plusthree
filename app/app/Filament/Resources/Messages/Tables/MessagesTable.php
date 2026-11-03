<?php

namespace App\Filament\Resources\Messages\Tables;

use App\Enums\MessageStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Models\Message;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('From')
                    ->description(fn (Message $record): string => $record->email)
                    ->searchable(['name', 'email'])
                    ->weight('bold'),
                TextColumn::make('role_title')
                    ->label('Role')
                    ->placeholder('—')
                    ->searchable()
                    ->description(fn (Message $record): string => $record->employmentTypeLabel() ?? 'Type not given'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (MessageStatus $state): string => $state->label())
                    ->color(fn (MessageStatus $state): string => $state === MessageStatus::New ? 'danger' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('j M, H:i')
                    ->timezone('Africa/Nairobi')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        MessageStatus::New->value => 'New',
                        MessageStatus::Handled->value => 'Handled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->url(fn (Message $record): string => EmailResource::getUrl('create', [
                        'to' => $record->email,
                        'subject' => $record->replySubject(),
                        'message' => $record->getKey(),
                    ])),
                ActionGroup::make([
                    Action::make('markHandled')
                        ->label('Mark handled')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn (Message $record): bool => $record->status === MessageStatus::New)
                        ->action(fn (Message $record) => $record->markHandled()),
                    Action::make('markNew')
                        ->label('Mark unread')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('gray')
                        ->visible(fn (Message $record): bool => $record->status === MessageStatus::Handled)
                        ->action(fn (Message $record) => $record->markNew()),
                    DeleteAction::make(),
                ])->tooltip('More actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
