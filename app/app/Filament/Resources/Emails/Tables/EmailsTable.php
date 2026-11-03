<?php

namespace App\Filament\Resources\Emails\Tables;

use App\Enums\EmailStatus;
use App\Models\Email;
use App\Support\EmailDelivery;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class EmailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('subject')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->description(fn (Email $record): string => $record->recipientCount()
                        .' '.($record->recipientCount() === 1 ? 'recipient' : 'recipients')
                        .' — '.$record->recipientSummary(2)),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (EmailStatus $state): string => $state->label())
                    ->color(fn (EmailStatus $state): string => match ($state) {
                        EmailStatus::Draft => 'gray',
                        EmailStatus::Sent => 'success',
                        EmailStatus::Failed => 'danger',
                    }),
                TextColumn::make('from_address')
                    ->label('From')
                    ->formatStateUsing(fn (Email $record): string => $record->senderAddress())
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi')
                    ->placeholder('Not sent')
                    ->sortable(),
                TextColumn::make('error')
                    ->label('Last error')
                    ->limit(60)
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Email $record): bool => ! $record->isSent()),
                self::previewAction(),
                self::sendAction(),
                DeleteAction::make()
                    ->visible(fn (Email $record): bool => $record->isDraft()),
            ]);
    }

    public static function previewAction(): Action
    {
        return Action::make('preview')
            ->label('Preview')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn (Email $record): string => $record->subject)
            ->modalContent(fn (Email $record): HtmlString => new HtmlString(
                view('emails.preview', ['email' => $record])->render()
            ))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public static function sendAction(): Action
    {
        return Action::make('send')
            ->label('Send')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Send this email?')
            ->modalDescription(fn (Email $record): string => 'It goes to '.$record->recipientSummary(5).'. Sending cannot be undone.')
            ->modalSubmitActionLabel('Send now')
            ->visible(fn (Email $record): bool => ! $record->isSent())
            ->action(function (Email $record): void {
                if (EmailDelivery::send($record)) {
                    Notification::make()
                        ->title('Email sent')
                        ->body('Sent to '.$record->recipientSummary(3).'.')
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Email could not be sent')
                    ->body($record->fresh()?->error)
                    ->danger()
                    ->persistent()
                    ->send();
            });
    }
}
