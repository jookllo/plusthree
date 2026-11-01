<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
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
            DeleteAction::make(),
        ];
    }
}
