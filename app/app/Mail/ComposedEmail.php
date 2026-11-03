<?php

namespace App\Mail;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * A message composed in the panel.
 *
 * The body is editor markup and is sanitised by the model before it reaches
 * the template, so the same allowlist that protects article pages also
 * protects outgoing mail.
 */
class ComposedEmail extends Mailable
{
    use Queueable;

    public function __construct(public Email $email) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->email->subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.composed',
            text: 'emails.composed-text',
        );
    }
}
