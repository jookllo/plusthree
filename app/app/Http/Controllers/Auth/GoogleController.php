<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Google sign-in for the publishing panel.
 *
 * Two separate decisions:
 *  - who may sign in: only addresses on config('admin.allowed_emails');
 *  - what they get: the panel still requires users.is_admin.
 *
 * A rejected address never creates a user, never starts a session, and is
 * logged so an unexpected attempt is visible.
 */
class GoogleController
{
    public function redirect(): RedirectResponse
    {
        $this->ensureConfigured();

        return Socialite::driver('google')
            ->redirectUrl($this->callbackUrl())
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $this->ensureConfigured();

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->callbackUrl())
                ->user();
        } catch (\Throwable $exception) {
            report($exception);

            return $this->failed('Google could not complete the sign-in. Please try again.');
        }

        $email = strtolower((string) $googleUser->getEmail());
        $verified = (bool) ($googleUser->user['email_verified'] ?? false);

        if (! $verified || ! $this->isAllowed($email)) {
            logger()->warning('Rejected Google sign-in for the panel.', [
                'email' => $email,
                'verified' => $verified,
            ]);

            throw new HttpException(403, 'That Google account is not authorised for this panel.');
        }

        $user = $this->findUser($googleUser, $email);

        $user->forceFill([
            'google_id' => $googleUser->getId(),
            'avatar_url' => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if (! $user->exists) {
            // Only ever reached for an allowlisted address.
            $user->forceFill(['is_admin' => true]);
        }

        $user->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended('/mdosi');
    }

    private function findUser(\Laravel\Socialite\Contracts\User $googleUser, string $email): User
    {
        // Match the Google identity first: an address can be reassigned.
        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            return $user;
        }

        $user = new User;
        $user->fill([
            'name' => $googleUser->getName() ?: $email,
            'email' => $email,
            'password' => null,
        ]);

        return $user;
    }

    private function isAllowed(string $email): bool
    {
        return $email !== '' && in_array($email, config('admin.allowed_emails'), true);
    }

    private function ensureConfigured(): void
    {
        if (! config('services.google.enabled')) {
            throw new HttpException(503, 'Google sign-in is disabled.');
        }

        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            throw new HttpException(503, 'Google sign-in is not configured.');
        }
    }

    private function callbackUrl(): string
    {
        return route('auth.google.callback');
    }

    private function failed(string $message): RedirectResponse
    {
        return redirect()
            ->route('filament.mdosi.auth.login')
            ->withErrors(['google' => $message]);
    }
}
