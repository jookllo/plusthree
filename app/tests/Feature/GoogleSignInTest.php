<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            // Google is off by default; these tests exercise the feature once
            // the owner turns it on.
            'services.google.enabled' => true,
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'admin.allowed_emails' => ['owner@example.com'],
        ]);
    }

    public function test_the_login_page_offers_google_sign_in(): void
    {
        $this->get('/mdosi/login')->assertOk()
            ->assertSee('Sign in with Google')
            ->assertSee(route('auth.google'), false);
    }

    public function test_an_allowlisted_address_creates_an_admin_and_signs_in(): void
    {
        $this->fakeGoogleUser(email: 'owner@example.com', id: 'google-1', name: 'Site Owner');

        $this->get('/mdosi/auth/google/callback')->assertRedirect('/mdosi');

        $user = User::where('email', 'owner@example.com')->firstOrFail();

        $this->assertTrue($user->is_admin);
        $this->assertSame('google-1', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_an_address_that_is_not_on_the_list_is_refused(): void
    {
        $this->fakeGoogleUser(email: 'stranger@example.com');

        $this->get('/mdosi/auth/google/callback')->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'stranger@example.com']);
        $this->assertGuest();
    }

    public function test_an_unverified_google_address_is_refused(): void
    {
        $this->fakeGoogleUser(email: 'owner@example.com', verified: false);

        $this->get('/mdosi/auth/google/callback')->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'owner@example.com']);
        $this->assertGuest();
    }

    public function test_a_returning_google_user_is_matched_by_google_id(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $user->forceFill(['google_id' => 'google-9', 'is_admin' => true])->save();

        $this->fakeGoogleUser(email: 'owner@example.com', id: 'google-9');

        $this->get('/mdosi/auth/google/callback')->assertRedirect('/mdosi');

        $this->assertSame(1, User::where('email', 'owner@example.com')->count());
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_an_existing_account_is_linked_without_gaining_admin_rights(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com']);
        $user->forceFill(['is_admin' => false])->save();
        $this->assertFalse($user->fresh()->is_admin);

        $this->fakeGoogleUser(email: 'owner@example.com', id: 'google-5');

        $this->get('/mdosi/auth/google/callback')->assertRedirect('/mdosi');

        $user->refresh();

        $this->assertSame('google-5', $user->google_id);
        $this->assertFalse($user->is_admin);
    }

    public function test_sign_in_is_unavailable_until_google_credentials_are_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/mdosi/auth/google')->assertStatus(503);
        $this->get('/mdosi/auth/google/callback')->assertStatus(503);
    }

    public function test_sign_in_stays_disabled_until_the_owner_turns_it_on(): void
    {
        config(['services.google.enabled' => false]);

        $this->get('/mdosi/login')->assertOk()
            ->assertDontSee('Sign in with Google');

        $this->get('/mdosi/auth/google')->assertStatus(503);
        $this->get('/mdosi/auth/google/callback')->assertStatus(503);
    }

    private function fakeGoogleUser(string $email, string $id = 'google-1', string $name = 'Owner', bool $verified = true): void
    {
        $user = (new SocialiteUser)->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'avatar' => 'https://example.com/avatar.png',
        ]);

        $user->setRaw([
            'email' => $email,
            'email_verified' => $verified,
        ]);

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('redirectUrl')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($user);
        $driver->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }
}
