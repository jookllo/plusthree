<?php

namespace App\Filament\Resources\Emails\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),
                TagsInput::make('recipients')
                    ->label('Recipients')
                    ->placeholder('name@example.com')
                    ->required()
                    ->nestedRecursiveRules(['email:rfc'])
                    ->helperText('Press Enter after each address. One email is sent per address.')
                    ->columnSpanFull(),
                RichEditor::make('body')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['h2', 'h3'],
                        ['bulletList', 'orderedList', 'blockquote'],
                        ['undo', 'redo'],
                    ])
                    ->floatingToolbars([
                        'paragraph' => ['bold', 'italic', 'underline', 'link'],
                    ])
                    ->helperText('Formatting is kept in the sent email. Images are not attached in this version.'),
            ]);
    }
}
