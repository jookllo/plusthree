<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\MessageStatus;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An enquiry sent through the public employer form.
 *
 * Messages are received, not published: they never appear on the public site
 * and are only readable in the panel.
 */
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'company',
        'role_title',
        'employment_type',
        'link',
        'body',
        'source',
        'status',
        'handled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'status' => MessageStatus::class,
            'handled_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUnread(Builder $query): void
    {
        $query->where('status', MessageStatus::New);
    }

    public function markHandled(): void
    {
        if ($this->status === MessageStatus::Handled) {
            return;
        }

        $this->forceFill([
            'status' => MessageStatus::Handled,
            'handled_at' => now(),
        ])->save();
    }

    public function markNew(): void
    {
        $this->forceFill([
            'status' => MessageStatus::New,
            'handled_at' => null,
        ])->save();
    }

    public function employmentTypeLabel(): ?string
    {
        return $this->employment_type?->label();
    }

    /**
     * Only http/https destinations are linked; anything else is dropped, the
     * same rule job listings use for application URLs.
     */
    public function safeLink(): ?string
    {
        $link = trim((string) $this->link);

        if ($link === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $link : null;
    }

    /**
     * A safe reply subject that cannot smuggle markup into a mail header.
     */
    public function replySubject(): string
    {
        $role = trim((string) $this->role_title);

        return $role === ''
            ? 'Your message to Plusthree'
            : 'Re: '.$role;
    }
}
