<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Support\NairobiTime;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    /**
     * The editor works in Nairobi time; the record is stored in UTC.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['starts_at'] = NairobiTime::toNairobi($this->record->starts_at)->format('Y-m-d H:i:s');

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['starts_at'] = NairobiTime::toUtc($data['starts_at']);

        return $data;
    }
}
