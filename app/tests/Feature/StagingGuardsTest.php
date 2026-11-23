<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The preview copy (Jaribu) must be hard to index and hard to stumble into.
 * Production keeps its own behaviour: the password is only enforced when the
 * application environment is staging.
 */
class StagingGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_copy_that_is_not_indexable_says_so_in_the_headers(): void
    {
        config(['seo.indexable' => false]);

        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('X-Robots-Tag')
            ->assertSee('Disallow: /');
    }

    public function test_an_indexable_copy_does_not_send_the_noindex_header(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/')
            ->assertOk()
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_the_preview_prompts_for_its_shared_password(): void
    {
        $this->app->detectEnvironment(fn (): string => 'staging');

        config([
            'staging.basic_auth.username' => 'jaribu',
            'staging.basic_auth.password' => 'a-preview-password',
        ]);

        $this->get('/')
            ->assertStatus(401)
            ->assertHeader('WWW-Authenticate');

        $this->withServerVariables([
            'PHP_AUTH_USER' => 'jaribu',
            'PHP_AUTH_PW' => 'wrong-password',
        ])->get('/')->assertStatus(401);

        $this->withServerVariables([
            'PHP_AUTH_USER' => 'jaribu',
            'PHP_AUTH_PW' => 'a-preview-password',
        ])->get('/')->assertOk();
    }

    public function test_production_never_asks_for_the_preview_password(): void
    {
        config([
            'staging.basic_auth.username' => 'jaribu',
            'staging.basic_auth.password' => 'a-preview-password',
        ]);

        $this->get('/')->assertOk();
    }
}
