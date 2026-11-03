<?php

namespace App\Filament\Resources\Emails;

use App\Filament\Resources\Emails\Pages\CreateEmail;
use App\Filament\Resources\Emails\Pages\EditEmail;
use App\Filament\Resources\Emails\Pages\ListEmails;
use App\Filament\Resources\Emails\Schemas\EmailForm;
use App\Filament\Resources\Emails\Tables\EmailsTable;
use App\Models\Email;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmailResource extends Resource
{
    protected static ?string $model = Email::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static ?string $modelLabel = 'email';

    protected static ?string $pluralModelLabel = 'emails';

    public static function form(Schema $schema): Schema
    {
        return EmailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmails::route('/'),
            'create' => CreateEmail::route('/create'),
            'edit' => EditEmail::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): string
    {
        return 'Inbox';
    }

    protected static ?int $navigationSort = 2;
}
