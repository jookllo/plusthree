<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_are_noindex_until_indexing_is_deliberately_enabled(): void
    {
        $this->assertFalse(config('seo.indexable'));

        $this->get('/')->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_indexing_is_enabled_by_configuration(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/')->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_filtered_searches_stay_noindex_even_when_the_site_is_indexable(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/jobs?q=designer&type=contract')->assertOk()
            ->assertSee('content="noindex, nofollow"', false);
    }

    public function test_canonical_urls_drop_filters_and_pagination(): void
    {
        $this->get('/jobs?q=designer&page=2')->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/jobs').'">', false);

        $article = Article::factory()->published()->create();

        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('blog.show', $article->slug).'">', false);
    }

    public function test_sitemap_lists_only_public_content(): void
    {
        $article = Article::factory()->published()->create(['title' => 'Published guide']);
        $draft = Article::factory()->create(['title' => 'Draft guide']);
        $job = JobListing::factory()->open()->create();
        $closed = JobListing::factory()->closed()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('blog.show', $article->slug), false)
            ->assertSee(route('jobs.show', $job->slug), false)
            ->assertDontSee(route('blog.show', $draft->slug), false)
            ->assertDontSee(route('jobs.show', $closed->slug), false)
            ->assertSee(route('home'), false);
    }

    public function test_robots_blocks_everything_while_the_site_is_private(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /')
            ->assertDontSee('Sitemap:');
    }

    public function test_robots_points_at_the_sitemap_once_indexable(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/robots.txt')->assertOk()
            ->assertSee('Allow: /')
            ->assertSee('Disallow: /mdosi')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_article_pages_emit_valid_article_structured_data(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'How to answer the salary question',
            'excerpt' => 'A practical script for the salary conversation.',
            'body' => '<p>Ask for the range first.</p>',
        ]);

        $json = $this->structuredData($this->get('/blog/'.$article->slug)->getContent());

        $this->assertSame('Article', $json['@type']);
        $this->assertSame('How to answer the salary question', $json['headline']);
        $this->assertSame($article->published_at->toIso8601String(), $json['datePublished']);
        $this->assertSame(route('blog.show', $article->slug), $json['mainEntityOfPage']['@id']);
    }

    public function test_job_pages_emit_valid_job_posting_structured_data(): void
    {
        $job = JobListing::factory()->open()->create([
            'title' => 'Product Designer',
            'location_city' => 'Nairobi',
            'location_country' => 'Kenya',
        ]);

        $json = $this->structuredData($this->get('/jobs/'.$job->slug)->getContent());

        $this->assertSame('JobPosting', $json['@type']);
        $this->assertSame('Product Designer', $json['title']);
        $this->assertSame('FULL_TIME', $json['employmentType']);
        $this->assertSame($job->company->name, $json['hiringOrganization']['name']);
        $this->assertSame('Nairobi', $json['jobLocation']['address']['addressLocality']);
        $this->assertSame($job->closing_at->toIso8601String(), $json['validThrough']);
    }

    public function test_structured_data_does_not_break_out_of_the_script_tag(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'A title with </script> inside',
            'body' => '<p>Body</p>',
        ]);

        $html = $this->get('/blog/'.$article->slug)->getContent();

        $this->assertStringNotContainsString('</script> inside', $html);
        $this->assertSame('A title with </script> inside', $this->structuredData($html)['headline']);
    }

    public function test_category_pages_appear_in_the_sitemap_only_when_they_have_published_articles(): void
    {
        $used = Category::factory()->article()->create(['name' => 'Interviews']);
        $empty = Category::factory()->article()->create(['name' => 'Salaries']);

        Article::factory()->published()->create()->categories()->attach($used);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(route('blog.category', $used->slug), false)
            ->assertDontSee(route('blog.category', $empty->slug), false);
    }

    /**
     * @return array<string, mixed>
     */
    private function structuredData(string $html): array
    {
        $this->assertMatchesRegularExpression('/<script type="application\/ld\+json">(.+?)<\/script>/s', $html);

        preg_match('/<script type="application\/ld\+json">(.+?)<\/script>/s', $html, $matches);

        $decoded = json_decode($matches[1], true);

        $this->assertIsArray($decoded, 'Structured data is not valid JSON.');

        return $decoded;
    }
}
