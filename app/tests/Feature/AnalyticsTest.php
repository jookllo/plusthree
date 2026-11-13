<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Cheki analytics and the session recorder are third-party scripts on every
 * public page. These tests pin where they may appear, because a stray copy in
 * the panel would record editorial work and a missing one silently loses data.
 */
class AnalyticsTest extends TestCase
{
    public function test_every_public_page_loads_both_tags_exactly_once(): void
    {
        config(['analytics.enabled' => true]);

        foreach (['/', '/jobs', '/blog', '/for-employers'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '/script.js'), $url.' should load script.js once');
            $this->assertSame(1, substr_count($html, '/recorder.js'), $url.' should load recorder.js once');
            $this->assertStringContainsString(
                'data-website-id="'.config('analytics.website_id').'"',
                $html,
                $url.' should identify the website',
            );
        }
    }

    public function test_the_tags_sit_in_the_head_and_are_deferred(): void
    {
        config(['analytics.enabled' => true]);

        $html = $this->get('/jobs')->assertOk()->getContent();

        $head = substr($html, 0, (int) strpos($html, '</head>'));

        $this->assertStringContainsString('<script defer src="'.config('analytics.host').'/script.js"', $head);
        $this->assertStringContainsString('<script defer src="'.config('analytics.host').'/recorder.js"', $head);
    }

    public function test_tracking_stays_off_outside_production(): void
    {
        // APP_ENV is testing here, so the default is off: development browsing
        // and the suite must not land in the owner's report.
        $this->assertFalse(config('analytics.enabled'));

        $this->get('/')->assertOk()->assertDontSee('cheki.plusthree.io', false);
    }

    public function test_the_editorial_panel_is_not_instrumented(): void
    {
        config(['analytics.enabled' => true]);

        $this->get('/mdosi/login')->assertOk()->assertDontSee('cheki.plusthree.io', false);
    }
}
