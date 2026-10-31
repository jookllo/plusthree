<?php

namespace App\Filament\Resources\JobListings\Pages;

use App\Filament\Resources\JobListings\JobListingResource;
use App\Support\NairobiTime;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateJobListing extends CreateRecord
{
    protected static string $resource = JobListingResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['closing_at'] = $this->closingDate($data);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function closingDate(array $data): ?\Carbon\CarbonImmutable
    {
        return filled($data['closing_at'] ?? null)
            ? NairobiTime::endOfDayUtc($data['closing_at'])
            : null;
    }
}
