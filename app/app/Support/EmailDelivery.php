<?php

namespace App\Support;

use App\Enums\EmailStatus;
use App\Mail\ComposedEmail;
use App\Models\Email;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends a panel email synchronously and records what happened.
 *
 * There is no queue worker on the deployment, so sending happens in the
 * request. Each recipient is attempted separately: one bad address must not
 * stop the others, and the record keeps the failure reason.
 */
final class EmailDelivery
{
    public static function send(Email $email): bool
    {
        $recipients = $email->recipientList();

        if ($recipients === []) {
            $email->forceFill([
                'status' => EmailStatus::Failed,
                'error' => 'This email has no recipients.',
            ])->save();

            return false;
        }

        $failures = [];

        foreach ($recipients as $address) {
            try {
                Mail::to($address)->send(new ComposedEmail($email));
            } catch (Throwable $exception) {
                report($exception);
                $failures[$address] = mb_substr(trim($exception->getMessage()), 0, 200);
            }
        }

        if ($failures !== []) {
            $delivered = count($recipients) - count($failures);

            $email->forceFill([
                'status' => EmailStatus::Failed,
                'error' => mb_substr(
                    ($delivered > 0 ? "Delivered to {$delivered} of ".count($recipients).' recipients. ' : '')
                    .self::describe($failures),
                    0,
                    1000,
                ),
            ])->save();

            return false;
        }

        $email->forceFill([
            'status' => EmailStatus::Sent,
            'error' => null,
            'sent_at' => now(),
        ])->save();

        return true;
    }

    /**
     * @param  array<string, string>  $failures
     */
    private static function describe(array $failures): string
    {
        return collect($failures)
            ->map(fn (string $reason, string $address): string => $address.': '.$reason)
            ->implode(' | ');
    }
}
