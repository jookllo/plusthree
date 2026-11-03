<?php

namespace App\Models;

use App\Enums\EmailStatus;
use App\Support\ArticleBody;
use Database\Factories\EmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

/**
 * An email composed in the panel.
 *
 * Drafts are reusable: the record is what you formatted, and sending it only
 * stamps a status and a time. Nothing is sent until an editor presses Send.
 */
class Email extends Model
{
    /** @use HasFactory<EmailFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subject',
        'body',
        'recipients',
        'status',
        'error',
        'sent_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'status' => EmailStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === EmailStatus::Draft;
    }

    public function isSent(): bool
    {
        return $this->status === EmailStatus::Sent;
    }

    /**
     * @return list<string>
     */
    public function recipientList(): array
    {
        $recipients = is_array($this->recipients) ? $this->recipients : [];

        return array_values(array_filter(array_map(
            fn ($address): string => strtolower(trim((string) $address)),
            $recipients,
        ), fn (string $address): bool => $address !== ''));
    }

    public function recipientCount(): int
    {
        return count($this->recipientList());
    }

    public function recipientSummary(int $limit = 3): string
    {
        $recipients = $this->recipientList();

        if ($recipients === []) {
            return 'No recipients yet';
        }

        if (count($recipients) <= $limit) {
            return implode(', ', $recipients);
        }

        return implode(', ', array_slice($recipients, 0, $limit))
            .' and '.(count($recipients) - $limit).' more';
    }

    /**
     * The body is editor-written markup, so it goes through the same
     * allowlist as articles before it is echoed or mailed.
     */
    public function safeBody(): HtmlString
    {
        return ArticleBody::render($this->body);
    }
}
