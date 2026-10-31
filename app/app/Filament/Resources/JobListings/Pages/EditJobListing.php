<?php

namespace App\Filament\Resources\JobListings\Pages;

use App\Filament\Resources\JobListings\JobListingResource;
use App\Support\NairobiTime;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditJobListing extends EditRecord
{
    protected static string $resource = JobListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();
        $data['closing_at'] = filled($data['closing_at'] ?? null)
            ? NairobiTime::endOfDayUtc($data['closing_at'])
            : null;

        return $data;
    }
}
