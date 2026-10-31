<?php

namespace Tests\Feature;

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
}
