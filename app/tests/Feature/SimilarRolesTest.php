<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The sidebar block that keeps someone on the site after they have read a
 * role: same sector first, then the same country, then the same arrangement.
 */
class SimilarRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_role_suggests_others_from_the_same_sector_first(): void
    {
        $sector = Category::factory()->job()->create(['name' => 'Design']);

        $current = JobListing::factory()->open()->create(['title' => 'Senior Product Designer']);
        $current->categories()->attach($sector);

        $related = JobListing::factory()->open()->create(['title' => 'Interaction Designer']);
        $related->categories()->attach($sector);

        JobListing::factory()->open()->create(['title' => 'Financial Accountant']);

        $list = $this->similarList($current);

        $this->assertStringContainsString('Interaction Designer', $list);
        $this->assertStringNotContainsString('Senior Product Designer', $list, 'The role being read is not its own suggestion.');
        $this->assertLessThan(
            strpos($list, 'Financial Accountant'),
            strpos($list, 'Interaction Designer'),
            'A sector match outranks the roles that only top the list up.',
        );
    }

    public function test_closed_roles_are_never_suggested(): void
    {
        $current = JobListing::factory()->open()->create(['title' => 'Programme Officer']);
        JobListing::factory()->closed()->create(['title' => 'Expired Programme Officer']);

        $list = $this->similarList($current);

        $this->assertStringNotContainsString('Expired Programme Officer', $list);
    }

    public function test_country_is_the_fallback_when_no_sector_is_tagged(): void
    {
        $current = JobListing::factory()->open()->create([
            'title' => 'Country Director',
            'location_country' => 'Kenya',
        ]);

        JobListing::factory()->open()->create([
            'title' => 'Kenyan Field Officer',
            'location_country' => 'Kenya',
        ]);

        JobListing::factory()->open()->create([
            'title' => 'Ugandan Field Officer',
            'location_country' => 'Uganda',
        ]);

        $list = $this->similarList($current);

        $this->assertStringContainsString('Kenyan Field Officer', $list);
        $this->assertLessThan(
            strpos($list, 'Ugandan Field Officer'),
            strpos($list, 'Kenyan Field Officer'),
            'Roles in the same country come before the wider fallback.',
        );
    }

    public function test_the_block_is_absent_when_there_is_nothing_to_suggest(): void
    {
        $only = JobListing::factory()->open()->create(['title' => 'The Only Open Role']);

        $this->get('/jobs/'.$only->slug)->assertOk()
            ->assertDontSee('Similar roles');
    }

    /**
     * Only the similar-roles card, so assertions cannot accidentally match the
     * job's own title in the header or structured data.
     */
    private function similarList(JobListing $job): string
    {
        $html = $this->get('/jobs/'.$job->slug)->assertOk()->getContent();

        return Str::between($html, 'id="similar-heading"', '</aside>');
    }
}
