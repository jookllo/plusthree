<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Models\Article;
use App\Models\Category;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_lists_only_open_jobs_and_published_articles(): void
    {
        JobListing::factory()->open()->create(['title' => 'Product Designer']);
        JobListing::factory()->create(['title' => 'Secret Draft Role']);
        JobListing::factory()->closed()->create(['title' => 'Expired Role']);
        Article::factory()->published()->create(['title' => 'How to write a CV that gets read']);
        Article::factory()->create(['title' => 'Unpublished Guide']);

        $this->get('/')->assertOk()
            ->assertSee('Product Designer')
            ->assertDontSee('Secret Draft Role')
            ->assertDontSee('Expired Role')
            ->assertSee('How to write a CV that gets read')
            ->assertDontSee('Unpublished Guide');
    }

    public function test_homepage_search_sends_visitors_to_the_job_board(): void
    {
        JobListing::factory()->open()->create(['title' => 'Product Designer']);

        $this->get('/')->assertOk()
            ->assertSee('action="'.route('jobs.index').'"', false)
            ->assertSee('name="q"', false);
    }

    public function test_every_page_links_the_site_icons_and_manifest(): void
    {
        // The icons are static files, so a broken reference is invisible to the
        // rest of the tests. The ?v= keeps Cloudflare from serving an old copy.
        $this->get('/')->assertOk()
            ->assertSee('rel="icon"', false)
            ->assertSee('favicon.ico?v=', false)
            ->assertSee('favicon-32.png', false)
            ->assertSee('apple-touch-icon.png', false)
            ->assertSee('rel="manifest"', false)
            ->assertSee('site.webmanifest', false)
            ->assertSee('name="theme-color"', false);
    }

    public function test_part_time_roles_can_be_filtered(): void
    {
        JobListing::factory()->open()->create([
            'title' => 'Part-time Editor',
            'employment_type' => EmploymentType::PartTime,
        ]);
        JobListing::factory()->open()->create(['title' => 'Full-time Editor']);

        $this->get('/jobs?type=part_time')->assertOk()
            ->assertSee('Part-time Editor')
            ->assertDontSee('Full-time Editor');
    }

    public function test_job_board_filters_by_keyword_and_contract_type(): void
    {
        JobListing::factory()->open()->create(['title' => 'Product Designer']);
        JobListing::factory()->open()->create(['title' => 'Programme Officer']);
        JobListing::factory()->open()->create([
            'title' => 'Research Intern',
            'employment_type' => EmploymentType::Internship,
        ]);

        $this->get('/jobs?q=designer')->assertOk()
            ->assertSee('Product Designer')
            ->assertDontSee('Programme Officer')
            ->assertDontSee('Research Intern');

        $this->get('/jobs?type=internship')->assertOk()
            ->assertSee('Research Intern')
            ->assertDontSee('Product Designer');
    }

    public function test_job_board_filters_fellowships_and_other_opportunities(): void
    {
        JobListing::factory()->open()->create(['title' => 'Product Designer']);
        JobListing::factory()->open()->create([
            'title' => 'Climate Leadership Fellow',
            'employment_type' => EmploymentType::Fellowship,
        ]);
        JobListing::factory()->open()->create([
            'title' => 'Community Grant',
            'employment_type' => EmploymentType::Other,
        ]);

        $this->get('/jobs?type=fellowship')->assertOk()
            ->assertSee('Climate Leadership Fellow')
            ->assertDontSee('Product Designer')
            ->assertDontSee('Community Grant');

        $this->get('/jobs?type=other')->assertOk()
            ->assertSee('Community Grant')
            ->assertDontSee('Climate Leadership Fellow')
            ->assertDontSee('Product Designer');
    }

    public function test_urgency_notice_counts_only_the_page_type(): void
    {
        JobListing::factory()->closingSoon()->create(['title' => 'Standard Urgent Role']);
        JobListing::factory()->closingSoon()->create([
            'title' => 'Fellowship Urgent Role',
            'employment_type' => EmploymentType::Fellowship,
        ]);

        // The board counts both urgent roles and points at the unfiltered view.
        $this->get('/jobs')->assertOk()
            ->assertSee('roles close within a week', false)
            ->assertSee('href="'.route('jobs.index', ['closing' => 'soon']).'"', false);

        // The Fellowships landing page must not advertise a standard job.
        $this->get('/fellowships')->assertOk()
            ->assertSee('role closes within a week', false)
            ->assertDontSee('roles close within a week', false)
            ->assertSee('href="'.str_replace('&', '&amp;', route('jobs.index', ['closing' => 'soon', 'type' => 'fellowship'])).'"', false);
    }

    public function test_header_links_job_categories_directly_and_an_employer_link(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Jobs')
            ->assertDontSee('Careers')
            ->assertSee('Fellowships')
            ->assertSee('Other opportunities')
            ->assertSee('href="'.route('jobs.index').'"', false)
            ->assertSee('href="'.route('jobs.fellowships').'"', false)
            ->assertSee('href="'.route('jobs.other').'"', false)
            ->assertSee('href="'.route('employers.show').'"', false)
            ->assertSee('For employers')
            // The owner asked for the three categories as a visible menu, not
            // a "Find a job" disclosure.
            ->assertDontSee('Find a job')
            ->assertDontSee('nav-menu', false)
            // The old keyword shortcuts were removed at the owner's request.
            ->assertDontSee('Design &amp; creative', false)
            ->assertDontSee('Social impact');
    }

    public function test_search_boxes_carry_a_hidden_label_and_no_prompt(): void
    {
        JobListing::factory()->open()->create(['title' => 'Product Designer']);

        $this->get('/')->assertOk()
            ->assertDontSee('What do you want to do?')
            ->assertSee('<label for="q" class="visually-hidden">Search jobs</label>', false);

        $this->get('/jobs')->assertOk()
            ->assertDontSee('What do you want to do?')
            ->assertSee('<label for="q" class="visually-hidden">Search jobs</label>', false);
    }

    public function test_homepage_paginates_its_roles_instead_of_linking_away(): void
    {
        JobListing::factory()->open()->count(7)->create();

        $this->get('/')->assertOk()
            ->assertSee('Page 1 of 2')
            ->assertSee('aria-label="Page 2"', false)
            ->assertDontSee('See all open roles');

        $this->get('/?page=2')->assertOk()
            ->assertSee('Page 2 of 2')
            ->assertSee('rel="prev"', false);
    }

    public function test_new_roles_stay_on_top_while_closing_soon_is_signposted(): void
    {
        // The urgent role is older: if the list sorted by deadline it would sit
        // above the fresh role, which is exactly what the owner reported.
        JobListing::factory()->closingSoon()->create([
            'title' => 'Older Urgent Role',
            'published_at' => now()->subDays(3),
        ]);
        JobListing::factory()->open()->create([
            'title' => 'Fresh Role',
            'published_at' => now()->subHour(),
        ]);

        foreach (['/', '/jobs'] as $url) {
            $this->get($url)->assertOk()
                ->assertSeeInOrder(['Fresh Role', 'Older Urgent Role'])
                ->assertSee('job-flag', false)
                ->assertDontSee('job-row--urgent', false)
                ->assertSee('role closes within a week', false)
                ->assertSee('closing=soon', false);
        }
    }

    public function test_urgency_notice_counts_open_roles_and_stays_off_the_filtered_view(): void
    {
        JobListing::factory()->closingSoon()->count(2)->create();
        JobListing::factory()->open()->create();

        $this->get('/jobs')->assertOk()
            ->assertSee('roles close within a week', false)
            ->assertSee('See what’s closing soon');

        $this->get('/jobs?closing=soon')->assertOk()
            ->assertDontSee('roles close within a week', false)
            ->assertDontSee('See what’s closing soon');
    }

    public function test_urgency_notice_is_hidden_when_nothing_is_closing_soon(): void
    {
        JobListing::factory()->open()->create();

        foreach (['/', '/jobs'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('urgency-bar', false)
                ->assertDontSee('See what’s closing soon');
        }
    }

    public function test_closing_soon_view_orders_by_the_nearest_deadline(): void
    {
        JobListing::factory()->closingSoon()->create([
            'title' => 'Six Days Left',
            'closing_at' => now()->addDays(6),
            'published_at' => now()->subHour(),
        ]);
        JobListing::factory()->closingSoon()->create([
            'title' => 'One Day Left',
            'closing_at' => now()->addDay(),
            'published_at' => now()->subDays(4),
        ]);

        $this->get('/jobs?closing=soon')->assertOk()
            ->assertSeeInOrder(['One Day Left', 'Six Days Left']);
    }

    public function test_sector_pages_list_only_roles_from_that_sector(): void
    {
        $sector = Category::factory()->job()->create(['name' => 'Social impact']);
        $other = Category::factory()->job()->create(['name' => 'Finance']);

        $included = JobListing::factory()->open()->create(['title' => 'Programme Officer']);
        $included->categories()->attach($sector);

        $excluded = JobListing::factory()->open()->create(['title' => 'Accountant']);
        $excluded->categories()->attach($other);

        $this->get('/jobs/sector/'.$sector->slug)->assertOk()
            ->assertSee('Social impact jobs')
            ->assertSee('Programme Officer')
            ->assertDontSee('Accountant');
    }

    public function test_sector_pages_stay_noindex_until_they_have_enough_roles(): void
    {
        config(['seo.indexable' => true]);

        $sector = Category::factory()->job()->create(['name' => 'Social impact']);
        JobListing::factory()->open()->create()->categories()->attach($sector);

        $this->get('/jobs/sector/'.$sector->slug)->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);

        JobListing::factory()->open()->count(2)->create()
            ->each(fn (JobListing $job) => $job->categories()->attach($sector));

        $this->get('/jobs/sector/'.$sector->slug)->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_country_pages_list_roles_in_that_country(): void
    {
        JobListing::factory()->open()->create([
            'title' => 'Nairobi Role',
            'location_city' => 'Nairobi',
            'location_country' => 'Kenya',
        ]);
        JobListing::factory()->open()->create([
            'title' => 'Kampala Role',
            'location_city' => 'Kampala',
            'location_country' => 'Uganda',
        ]);

        $this->get('/jobs/in/kenya')->assertOk()
            ->assertSee('Jobs in Kenya')
            ->assertSee('Nairobi Role')
            ->assertDontSee('Kampala Role');
    }

    public function test_a_country_page_that_does_not_exist_is_not_found(): void
    {
        JobListing::factory()->open()->create(['location_country' => 'Kenya']);

        $this->get('/jobs/in/atlantis')->assertNotFound();
        $this->get('/jobs/sector/no-such-sector')->assertNotFound();
    }

    public function test_the_board_links_to_its_sector_and_country_pages(): void
    {
        $sector = Category::factory()->job()->create(['name' => 'Social impact']);
        $job = JobListing::factory()->open()->create(['location_country' => 'Kenya']);
        $job->categories()->attach($sector);

        $this->get('/jobs')->assertOk()
            ->assertSee('Browse by sector')
            ->assertSee('Browse by country')
            ->assertSee('href="'.route('jobs.sector', $sector->slug).'"', false)
            ->assertSee('href="'.route('jobs.country', 'kenya').'"', false);

        // The detail page links back to both, so the pages are not orphaned.
        $this->get('/jobs/'.$job->slug)->assertOk()
            ->assertSee('href="'.route('jobs.sector', $sector->slug).'"', false)
            ->assertSee('href="'.route('jobs.country', 'kenya').'"', false);
    }

    public function test_homepage_latest_opportunities_does_not_count_roles(): void
    {
        JobListing::factory()->open()->count(3)->create();

        $this->get('/')->assertOk()
            ->assertDontSee('3 roles')
            ->assertDontSee('1 role');
    }

    public function test_job_board_uses_numbered_pagination(): void
    {
        JobListing::factory()->open()->count(12)->create();

        $this->get('/jobs')->assertOk()
            ->assertSee('Page 1 of 2')
            ->assertSee('rel="next"', false)
            ->assertSee('aria-label="Page 2"', false);

        $this->get('/jobs?page=2')->assertOk()
            ->assertSee('Page 2 of 2')
            ->assertSee('rel="prev"', false);
    }

    public function test_jobs_closing_within_a_week_are_flagged_and_filterable(): void
    {
        $soon = JobListing::factory()->closingSoon()->create(['title' => 'Programme Officer']);
        JobListing::factory()->open()->create(['title' => 'Product Designer']);

        $this->get('/jobs')->assertOk()
            ->assertSee('Programme Officer')
            ->assertSee('Closing soon')
            ->assertDontSee('Closes today');

        $this->get('/jobs?closing=soon')->assertOk()
            ->assertSee('Programme Officer')
            ->assertDontSee('Product Designer');

        $this->get('/jobs/'.$soon->slug)->assertOk()
            ->assertSee('Closing soon')
            ->assertSee('applications close', false);
    }

    public function test_archived_job_stays_readable_but_is_not_advertised(): void
    {
        $archived = JobListing::factory()->archived()->create(['title' => 'Retired Role']);

        $this->get('/jobs/'.$archived->slug)->assertOk()
            ->assertSee('Retired Role')
            ->assertSee('no longer available')
            ->assertDontSee('Apply on employer site');

        $this->get('/jobs')->assertOk()->assertDontSee('Retired Role');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee(route('jobs.show', $archived->slug), false);
    }

    public function test_job_detail_supports_rich_text_a_featured_image_and_seo_overrides(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('jobs/featured.jpg', 'fake-image');

        $job = JobListing::factory()->open()->create([
            'title' => 'Programme Officer',
            'description' => '<p>Lead <strong>field work</strong>.</p><script>alert(1)</script>',
            'featured_image_path' => 'jobs/featured.jpg',
            'featured_image_alt' => 'A team reviewing a map outdoors',
            'seo_title' => 'Programme Officer in Nairobi',
            'seo_description' => 'Lead field work across Kenya.',
        ]);

        $this->get('/jobs/'.$job->slug)->assertOk()
            ->assertSee('<strong>field work</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('jobs/featured.jpg', false)
            ->assertSee('alt="A team reviewing a map outdoors"', false)
            ->assertSee('<title>Programme Officer in Nairobi — Plusthree</title>', false)
            ->assertSee('name="description" content="Lead field work across Kenya."', false);
    }

    public function test_draft_job_preview_requires_a_signature(): void
    {
        $draft = JobListing::factory()->create(['title' => 'Secret Draft Role']);

        $this->get('/jobs/'.$draft->slug.'/preview')->assertForbidden();

        $url = URL::temporarySignedRoute('jobs.preview', now()->addMinutes(10), ['slug' => $draft->slug]);

        $this->get($url)->assertOk()
            ->assertSee('Secret Draft Role')
            ->assertSee('this listing is not published');
    }

    public function test_fellowships_and_other_opportunities_have_their_own_pages(): void
    {
        JobListing::factory()->open()->create(['title' => 'Product Designer']);
        JobListing::factory()->open()->create([
            'title' => 'Climate Leadership Fellow',
            'employment_type' => EmploymentType::Fellowship,
        ]);
        JobListing::factory()->open()->create([
            'title' => 'Community Grant',
            'employment_type' => EmploymentType::Other,
        ]);

        $this->get('/fellowships')->assertOk()
            ->assertSee('Climate Leadership Fellow')
            ->assertSee('Fellowships')
            ->assertDontSee('Product Designer')
            ->assertDontSee('Community Grant')
            ->assertDontSee('name="type"', false);

        $this->get('/other-opportunities')->assertOk()
            ->assertSee('Community Grant')
            ->assertSee('Other opportunities')
            ->assertDontSee('Climate Leadership Fellow')
            ->assertDontSee('Product Designer')
            ->assertDontSee('name="type"', false);
    }

    public function test_landing_pages_search_inside_their_own_type(): void
    {
        JobListing::factory()->open()->create([
            'title' => 'Climate Leadership Fellow',
            'employment_type' => EmploymentType::Fellowship,
        ]);
        JobListing::factory()->open()->create([
            'title' => 'Community Grant',
            'employment_type' => EmploymentType::Other,
        ]);

        $this->get('/fellowships?q=grant')->assertOk()
            ->assertSee('Nothing here yet.')
            ->assertDontSee('Community Grant');

        $this->get('/fellowships?q=climate')->assertOk()
            ->assertSee('Climate Leadership Fellow');
    }

    public function test_job_board_empty_state_and_escaped_input(): void
    {
        JobListing::factory()->open()->create(['title' => 'Product Designer']);

        $this->get('/jobs?q=nonexistent')->assertOk()
            ->assertSee('No roles match that search.');

        $this->get('/jobs?q='.urlencode('<script>alert(1)</script>'))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_open_job_detail_offers_application_link(): void
    {
        $job = JobListing::factory()->open()->create([
            'title' => 'Product Designer',
            'application_url' => 'https://example.com/apply',
        ]);

        $this->get('/jobs/'.$job->slug)->assertOk()
            ->assertSee('Product Designer')
            ->assertSee('https://example.com/apply', false)
            ->assertDontSee('no longer accepting applications');
    }

    public function test_closed_job_stays_readable_but_cannot_be_applied_to(): void
    {
        $job = JobListing::factory()->closed()->create([
            'title' => 'Product Designer',
            'application_url' => 'https://example.com/apply',
        ]);

        $this->get('/jobs/'.$job->slug)->assertOk()
            ->assertSee('Product Designer')
            ->assertSee('no longer accepting applications')
            ->assertDontSee('https://example.com/apply', false);
    }

    public function test_draft_and_scheduled_jobs_are_not_public(): void
    {
        $draft = JobListing::factory()->create(['title' => 'Draft Role']);
        $scheduled = JobListing::factory()->published()->create([
            'title' => 'Scheduled Role',
            'published_at' => now()->addDays(2),
        ]);

        $this->get('/jobs/'.$draft->slug)->assertNotFound();
        $this->get('/jobs/'.$scheduled->slug)->assertNotFound();
    }

    public function test_job_board_escapes_application_url_schemes(): void
    {
        $job = JobListing::factory()->open()->create([
            'application_url' => 'javascript:alert(1)',
        ]);

        $this->get('/jobs/'.$job->slug)->assertOk()
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_blog_index_uses_newest_published_article_as_lead(): void
    {
        $older = Article::factory()->published()->create([
            'title' => 'Older Guide',
            'published_at' => now()->subDays(5),
        ]);
        $newest = Article::factory()->published()->create([
            'title' => 'Newest Guide',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/blog')->assertOk();

        $response->assertSee('Newest Guide');

        $this->assertSame('Newest Guide', $response->viewData('articles')->first()->title);
        $this->assertTrue($newest->isPublished());
        $this->assertTrue($older->isPublished());
    }

    public function test_article_page_and_related_articles(): void
    {
        $category = Category::factory()->article()->create(['name' => 'Interviews']);
        $article = Article::factory()->published()->create(['title' => 'Answering the salary question']);
        $article->categories()->attach($category);

        $related = Article::factory()->published()->create(['title' => 'Researching an employer']);
        $related->categories()->attach($category);

        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertSee('Answering the salary question')
            ->assertSee('Researching an employer');
    }

    public function test_category_page_lists_only_its_articles(): void
    {
        $interviews = Category::factory()->article()->create(['name' => 'Interviews']);
        $cv = Category::factory()->article()->create(['name' => 'CVs']);

        $inCategory = Article::factory()->published()->create(['title' => 'Interview Guide']);
        $inCategory->categories()->attach($interviews);

        $other = Article::factory()->published()->create(['title' => 'CV Guide']);
        $other->categories()->attach($cv);

        $this->get('/blog/category/'.$interviews->slug)->assertOk()
            ->assertSee('Interview Guide')
            ->assertDontSee('CV Guide');
    }

    public function test_unpublished_article_is_not_public(): void
    {
        $draft = Article::factory()->create(['title' => 'Draft Guide']);

        $this->get('/blog/'.$draft->slug)->assertNotFound();
    }

    public function test_articles_without_images_keep_the_hero_but_not_list_art(): void
    {
        $article = Article::factory()->published()->create(['title' => 'No photograph']);

        // The article page keeps a generated backdrop behind the headline.
        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertSee('article-hero-media', false)
            ->assertSee('article-art art-p', false);

        // Lists show a photograph or nothing at all.
        $this->get('/blog')->assertOk()
            ->assertDontSee('article-art art-p', false);
    }

    public function test_featured_image_renders_with_alt_text(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('articles/featured.jpg', 'fake-image');

        $article = Article::factory()->published()->create([
            'title' => 'Article with a photograph',
            'featured_image_path' => 'articles/featured.jpg',
            'featured_image_alt' => 'A desk with a notebook and a laptop',
        ]);

        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertSee('articles/featured.jpg', false)
            ->assertSee('alt="A desk with a notebook and a laptop"', false);

        $this->get('/blog')->assertOk()
            ->assertSee('articles/featured.jpg', false);
    }
}
