<?php

namespace Tests\Feature;

use Tests\TestCase;

class DiscoveryPreviewTest extends TestCase
{
    public function test_search_filters_sample_roles_and_keeps_them_labelled(): void
    {
        $this->get('/?q=designer')->assertOk()
            ->assertSee('Product Designer')->assertDontSee('Programme Officer')
            ->assertSee('not live vacancies')->assertSee('value="designer"', false);
    }

    public function test_type_filter_and_empty_state(): void
    {
        $this->get('/?type=Internship')->assertOk()->assertSee('Research Intern')->assertDontSee('Product Designer');
        $this->get('/?q=nonexistent')->assertOk()->assertSee('No sample roles match this search.');
    }

    public function test_search_input_is_escaped(): void
    {
        $this->get('/?q='.urlencode('<script>alert(1)</script>'))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
