<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapEmptyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_renders_with_no_content(): void
    {
        $this->withoutExceptionHandling();
        $this->get('/sitemap.xml')->assertOk();
    }
}
