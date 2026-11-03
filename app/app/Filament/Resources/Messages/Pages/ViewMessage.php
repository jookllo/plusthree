<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Enums\MessageStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Filament\Resources\Messages\MessageResource;
use App\Models\Message;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-paper-airplane')
                ->url(fn (Message $record): string => EmailResource::getUrl('create', [
                    'to' => $record->email,
                    'subject' => $record->replySubject(),
                    'message' => $record->getKey(),
                ])),
            Action::make('markHandled')
                ->label('Mark handled')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Message $record): bool => $record->status === MessageStatus::New)
                ->action(function (Message $record): void {
                    $record->markHandled();
                    $this->refreshRecord();
                }),
            Action::make('markNew')
                ->label('Mark unread')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->visible(fn (Message $record): bool => $record->status === MessageStatus::Handled)
                ->action(function (Message $record): void {
                    $record->markNew();
                    $this->refreshRecord();
                }),
            DeleteAction::make()
                ->successRedirectUrl(MessageResource::getUrl('index')),
        ];
    }

    private function refreshRecord(): void
    {
        $this->record->refresh();
    }
}
