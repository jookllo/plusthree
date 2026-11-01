<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Support\ImageDescriptions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\URL;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->tooltip('Published articles open on the public site; drafts open on a private link valid for 30 minutes.')
                ->url(fn (Article $record): string => $record->isPublished()
                    ? route('blog.show', $record->slug)
                    : URL::temporarySignedRoute('blog.preview', now()->addMinutes(30), ['slug' => $record->slug]))
                ->openUrlInNewTab(),
            $this->describeImagesAction(),
            DeleteAction::make(),
        ];
    }

    /**
     * The editor's image button does not ask for alt text, so this collects it
     * afterwards and writes it back into the body.
     */
    private function describeImagesAction(): Action
    {
        return Action::make('describeImages')
            ->label('Describe images')
            ->icon('heroicon-o-photo')
            ->color('warning')
            ->visible(fn (): bool => $this->missingImageDescriptions() !== [])
            ->modalHeading('Describe the images in this article')
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
                // Only the form state changes; the article is saved by the
                // editor's own Save action so unsaved typing is never lost.
                $this->data['body'] = ImageDescriptions::apply($this->bodyHtml(), $data['images'] ?? []);

                Notification::make()
                    ->title('Image descriptions added')
                    ->body('Save the article to keep them.')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return list<array{src: string, tag: string}>
     */
    private function missingImageDescriptions(): array
    {
        return ImageDescriptions::missing($this->bodyHtml());
    }

    /**
     * The editor keeps its working state as a TipTap document, so ask the form
     * for the dehydrated HTML rather than reading the raw array.
     */
    private function bodyHtml(): string
    {
        try {
            $body = $this->form->getState()['body'] ?? null;
        } catch (\Throwable) {
            $body = null;
        }

        if (is_string($body)) {
            return $body;
        }

        return (string) ($this->record?->body ?? '');
    }
}
