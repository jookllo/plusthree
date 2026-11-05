<?php

namespace App\Filament\Resources\JobListings\Pages;

use App\Filament\Resources\JobListings\JobListingResource;
use App\Models\JobListing;
use App\Support\ImageDescriptions;
use App\Support\NairobiTime;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class EditJobListing extends EditRecord
{
    protected static string $resource = JobListingResource::class;

    /**
     * Rich-text fields that can carry images without alt text.
     *
     * @var list<string>
     */
    private const RICH_FIELDS = ['description', 'requirements'];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->tooltip('Published listings open on the public site; drafts open on a private link valid for 30 minutes.')
                ->url(fn (JobListing $record): string => $record->isPublished()
                    ? route('jobs.show', $record->slug)
                    : URL::temporarySignedRoute('jobs.preview', now()->addMinutes(30), ['slug' => $record->slug]))
                ->openUrlInNewTab(),
            $this->describeImagesAction(),
            DeleteAction::make(),
        ];
    }

    /**
     * The editor's image button does not ask for alt text, so this collects it
     * afterwards and writes it back into the description or requirements.
     */
    private function describeImagesAction(): Action
    {
        return Action::make('describeImages')
            ->label('Describe images')
            ->icon('heroicon-o-photo')
            ->color('warning')
            ->visible(fn (): bool => $this->missingImageDescriptions() !== [])
            ->modalHeading('Describe the images in this listing')
            ->modalDescription('Screen readers read these aloud. Describe what the image shows, not that it is an image.')
            ->modalSubmitActionLabel('Add descriptions')
            ->fillForm(fn (): array => [
                'images' => array_map(
                    fn (array $image): array => ['src' => $image['src'], 'alt' => ''],
                    $this->missingImageDescriptions(),
                ),
            ])
            ->form([
                Repeater::make('images')
                    ->hiddenLabel()
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->schema([
                        TextInput::make('alt')
                            ->label('Description')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('src')
                            ->label('Image')
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ])
            ->action(function (array $data): void {
                // Only the form state changes; the listing is saved by the
                // editor's own Save action so unsaved typing is never lost.
                foreach (self::RICH_FIELDS as $field) {
                    $this->data[$field] = ImageDescriptions::apply($this->bodyHtml($field), $data['images'] ?? []);
                }

                Notification::make()
                    ->title('Image descriptions added')
                    ->body('Save the listing to keep them.')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return list<array{src: string, tag: string}>
     */
    private function missingImageDescriptions(): array
    {
        $missing = [];

        foreach (self::RICH_FIELDS as $field) {
            $missing = array_merge($missing, ImageDescriptions::missing($this->bodyHtml($field)));
        }

        return $missing;
    }

    /**
     * The editor keeps its working state as a TipTap document, so ask the form
     * for the dehydrated HTML rather than reading the raw array.
     */
    private function bodyHtml(string $field): string
    {
        try {
            $body = $this->form->getState()[$field] ?? null;
        } catch (\Throwable) {
            $body = null;
        }

        if (is_string($body)) {
            return $body;
        }

        return (string) ($this->record?->{$field} ?? '');
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
