<?php

namespace App\Filament\Resources\Emails\Pages;

use App\Enums\EmailStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Models\Email;
use App\Models\Message;
use Filament\Resources\Pages\CreateRecord;

class CreateEmail extends CreateRecord
{
    protected static string $resource = EmailResource::class;

    /**
     * Reply links pass the recipient and subject, plus the message id so the
     * original text is quoted in the body.
     */
    public function mount(): void
    {
        parent::mount();

        $message = $this->quotedMessage();
        $to = trim((string) request()->query('to'));

        $this->form->fill([
            'recipients' => $to !== '' ? [$to] : [],
            'subject' => trim((string) request()->query('subject')),
            'body' => $this->initialBody($message),
            'from_address' => Email::configuredSenders()[0] ?? null,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = EmailStatus::Draft;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return EmailResource::getUrl('edit', ['record' => $this->getRecord()]);
    }

    private function quotedMessage(): ?Message
    {
        $id = (int) request()->query('message');

        return $id > 0 ? Message::query()->find($id) : null;
    }

    private function initialBody(?Message $message): string
    {
        if (! $message) {
            return '<p></p>';
        }

        return '<p></p>'
            .'<p><strong>'.e($message->name).' wrote on '
            .$message->created_at->timezone('Africa/Nairobi')->format('j M Y').':</strong></p>'
            .'<blockquote><p>'.nl2br(e($message->body)).'</p></blockquote>';
    }
}
