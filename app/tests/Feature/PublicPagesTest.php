<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Models\Article;
use App\Models\Category;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
