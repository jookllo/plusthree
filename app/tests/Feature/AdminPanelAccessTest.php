<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_panel_login(): void
    {
        $this->get('/mdosi')->assertRedirect('/mdosi/login');
    }

    public function test_signed_in_non_admins_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isAdmin());

        $this->actingAs($user)->get('/mdosi')->assertForbidden();
    }

    public function test_explicit_admins_can_open_the_panel(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->get('/mdosi')->assertOk();
    }

    public function test_the_default_admin_path_stays_unused(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->get('/admin')->assertNotFound();
    }

    public function test_public_resources_are_not_reachable_without_the_panel_prefix(): void
    {
        $this->get('/job-listings')->assertNotFound();
        $this->get('/articles')->assertNotFound();
    }

    public function test_the_article_editor_offers_formatting_and_a_draft_preview(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $article = Article::factory()->create(['title' => 'Plain draft']);

        $this->actingAs($admin)
            ->get('/mdosi/articles/'.$article->slug.'/edit')
            ->assertOk()
            ->assertSee('rich-editor', false)
            ->assertSee('Preview');
    }

    public function test_the_google_button_is_hidden_until_it_is_configured(): void
    {
        config([
            'services.google.enabled' => false,
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
        ]);

        $this->get('/mdosi/login')->assertOk()
            ->assertDontSee('Sign in with Google')
            ->assertSee('Sign in');
    }

    public function test_the_google_button_appears_once_it_is_enabled_and_configured(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
        ]);

        $this->get('/mdosi/login')->assertOk()
            ->assertSee('Sign in with Google');
    }

    public function test_the_panel_offers_a_password_reset_request_page(): void
    {
        $this->get('/mdosi/password-reset/request')->assertOk()
            ->assertSee('Email');
    }
}
