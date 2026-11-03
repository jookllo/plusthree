<?php

namespace App\Filament\Resources\Emails\Pages;

use App\Filament\Resources\Emails\EmailResource;
use App\Filament\Resources\Emails\Tables\EmailsTable;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmail extends EditRecord
{
    protected static string $resource = EmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EmailsTable::previewAction(),
            EmailsTable::sendAction(),
            DeleteAction::make(),
        ];
    }
}
