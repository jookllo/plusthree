<?php

namespace App\Filament\Resources\Messages\Schemas;

use App\Enums\EmploymentType;
use App\Enums\MessageStatus;
use App\Models\Message;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('From'),
                TextEntry::make('email')
                    ->copyable(),
                TextEntry::make('company')
                    ->placeholder('Not given'),
                TextEntry::make('role_title')
                    ->label('Role')
                    ->placeholder('Not given'),
                TextEntry::make('employment_type')
                    ->label('Opportunity type')
                    ->badge()
                    ->formatStateUsing(fn (?EmploymentType $state): string => $state?->label() ?? 'Not given'),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (MessageStatus $state): string => $state->label())
                    ->color(fn (MessageStatus $state): string => $state === MessageStatus::New ? 'danger' : 'gray'),
                TextEntry::make('link')
                    ->label('Link')
                    ->url(fn (Message $record): ?string => $record->safeLink())
                    ->openUrlInNewTab()
                    ->placeholder('None given')
                    ->columnSpanFull(),
                TextEntry::make('body')
                    ->label('Message')
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'white-space:pre-line']),
                TextEntry::make('created_at')
                    ->label('Received')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi'),
                TextEntry::make('handled_at')
                    ->label('Handled')
                    ->dateTime('j M Y H:i')
                    ->timezone('Africa/Nairobi')
                    ->placeholder('Not yet'),
            ])
            ->columns(2);
    }
}
