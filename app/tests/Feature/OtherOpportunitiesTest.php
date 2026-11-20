<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Other opportunities" is one page over four types: consultancy, funding,
 * board seats and the catch-all Other. They must not become job sectors, and
 * the page's chips must narrow it without letting another type in.
 */
class OtherOpportunitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_groups_consultancy_funding_board_and_other(): void
    {
        $this->openRole('A Consultancy Call', EmploymentType::Consultancy);
        $this->openRole('A Funding Round', EmploymentType::Funding);
        $this->openRole('A Board Seat', EmploymentType::Board);
        $this->openRole('A Volunteer Role', EmploymentType::Other);
        $this->openRole('A Fellowship', EmploymentType::Fellowship);
        $this->openRole('A Standard Job', EmploymentType::FullTime);

        $this->get('/other-opportunities')->assertOk()
            ->assertSee('A Consultancy Call')
            ->assertSee('A Funding Round')
            ->assertSee('A Board Seat')
            ->assertSee('A Volunteer Role')
            ->assertDontSee('A Fellowship')
            ->assertDontSee('A Standard Job')
            // One chip per kind, plus "All".
            ->assertSee('href="'.route('jobs.other', ['type' => 'consultancy']).'"', false)
            ->assertSee('href="'.route('jobs.other', ['type' => 'funding']).'"', false)
            ->assertSee('href="'.route('jobs.other', ['type' => 'board']).'"', false)
            ->assertSee('href="'.route('jobs.other', ['type' => 'other']).'"', false)
            ->assertSee('aria-current="page">All</a>', false);
    }

    public function test_a_chip_narrows_the_page_and_stays_out_of_the_index(): void
    {
        $this->openRole('A Consultancy Call', EmploymentType::Consultancy);
        $this->openRole('A Funding Round', EmploymentType::Funding);

        $this->get('/other-opportunities?type=consultancy')->assertOk()
            ->assertSee('A Consultancy Call')
            ->assertDontSee('A Funding Round')
            ->assertSee('aria-current="page">Consultancy</a>', false)
            // ?type= is a filter: no duplicate page for search engines.
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('<link rel="canonical" href="'.route('jobs.other').'">', false);
    }

    public function test_a_type_outside_the_group_cannot_widen_the_page(): void
    {
        $this->openRole('A Consultancy Call', EmploymentType::Consultancy);
        $this->openRole('A Fellowship', EmploymentType::Fellowship);

        $this->get('/other-opportunities?type=fellowship')->assertOk()
            ->assertSee('A Consultancy Call')
            ->assertDontSee('A Fellowship')
            ->assertSee('aria-current="page">All</a>', false);
    }

    public function test_the_board_filter_offers_the_new_types_and_keeps_the_header_honest(): void
    {
        $this->openRole('A Funding Round', EmploymentType::Funding);
        $this->openRole('A Board Seat', EmploymentType::Board);

        $this->get('/jobs?type=funding')->assertOk()
            ->assertSee('A Funding Round')
            ->assertDontSee('A Board Seat')
            ->assertSee('aria-current="page" >Other opportunities</a>', false);
    }

    public function test_the_employer_form_offers_the_same_types(): void
    {
        $this->get('/for-employers')->assertOk()
            ->assertSee('<option value="consultancy"', false)
            ->assertSee('<option value="funding"', false)
            ->assertSee('<option value="board"', false);
    }

    private function openRole(string $title, EmploymentType $type): JobListing
    {
        return JobListing::factory()->open()->create([
            'title' => $title,
            'employment_type' => $type,
        ]);
    }
}
