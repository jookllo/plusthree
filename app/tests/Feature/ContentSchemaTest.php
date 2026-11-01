<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Enums\WorkMode;
use App\Models\Article;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\Tag;
use App\Support\NairobiTime;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_slugs_are_generated_and_made_unique(): void
    {
        $first = Company::factory()->create(['name' => 'Acme Ltd']);
        $second = Company::factory()->create(['name' => 'Acme Ltd']);

        $this->assertSame('acme-ltd', $first->slug);
        $this->assertSame('acme-ltd-2', $second->slug);
    }

    public function test_category_slugs_are_unique_per_kind_only(): void
    {
        $jobCategory = Category::factory()->job()->create(['name' => 'Design']);
        $articleCategory = Category::factory()->article()->create(['name' => 'Design']);

        $this->assertSame('design', $jobCategory->slug);
        $this->assertSame('design', $articleCategory->slug);

        $this->expectException(QueryException::class);
        Category::factory()->job()->create(['name' => 'Design', 'slug' => 'design']);
    }

    public function test_company_with_listings_cannot_be_deleted(): void
    {
        $company = Company::factory()->create();
        JobListing::factory()->open()->create(['company_id' => $company->id]);

        $this->expectException(QueryException::class);
        $company->delete();
    }

    public function test_deleting_an_author_keeps_the_article(): void
    {
        $article = Article::factory()->published()->create();
        $author = $article->author;

        $author->delete();

        $article->refresh();

        $this->assertNull($article->author_id);
        $this->assertSame(PublicationStatus::Published, $article->status);
    }

    public function test_categories_and_tags_attach_and_detach_from_listings(): void
    {
        $job = JobListing::factory()->open()->create();
        $category = Category::factory()->job()->create();
        $tag = Tag::factory()->create();

        $job->categories()->attach($category);
        $job->tags()->attach($tag);

        $this->assertTrue($job->fresh()->categories->contains($category));
        $this->assertTrue($job->fresh()->tags->contains($tag));

        $job->categories()->detach();

        $this->assertCount(0, $job->fresh()->categories);
        $this->assertCount(1, $job->fresh()->tags);
    }

    public function test_enum_values_stay_in_step_with_the_public_filters(): void
    {
        $this->assertSame(['full_time', 'contract', 'internship'], EmploymentType::values());
        $this->assertSame('Full-time', EmploymentType::FullTime->label());
        $this->assertSame('Contract', EmploymentType::Contract->label());
        $this->assertSame('Internship', EmploymentType::Internship->label());

        $this->assertSame(['on_site', 'hybrid', 'remote'], WorkMode::values());
        $this->assertSame('On-site', WorkMode::OnSite->label());
        $this->assertSame('Hybrid', WorkMode::Hybrid->label());
        $this->assertSame('Remote', WorkMode::Remote->label());

        $this->assertSame(['draft', 'published', 'archived'], PublicationStatus::values());
    }

    public function test_unsafe_application_urls_are_dropped(): void
    {
        $job = JobListing::factory()->create(['application_url' => 'javascript:alert(1)']);

        $this->assertNull($job->safeApplicationUrl());
        $this->assertFalse($job->canApply());

        $job->update(['application_url' => 'https://example.com/apply']);
        $job->publish();

        $this->assertSame('https://example.com/apply', $job->fresh()->safeApplicationUrl());
        $this->assertTrue($job->fresh()->canApply());
    }

    public function test_email_only_application_is_allowed(): void
    {
        $job = JobListing::factory()->open()->create([
            'application_url' => null,
            'application_email' => 'jobs@example.com',
        ]);

        $this->assertTrue($job->canApply());
    }

    public function test_seo_fields_fall_back_to_title_and_excerpt_then_override(): void
    {
        $article = Article::factory()->create([
            'title' => 'A CV that tells your story',
            'excerpt' => 'Make your experience easy to understand.',
        ]);

        $this->assertSame('A CV that tells your story', $article->metaTitle());
        $this->assertSame('Make your experience easy to understand.', $article->metaDescription());

        $article->update([
            'seo_title' => 'CV tips that work',
            'seo_description' => 'A short search description.',
        ]);

        $this->assertSame('CV tips that work', $article->fresh()->metaTitle());
        $this->assertSame('A short search description.', $article->fresh()->metaDescription());
    }

    public function test_reading_time_is_derived_from_the_body(): void
    {
        $article = Article::factory()->create(['body' => str_repeat('word ', 400)]);

        $this->assertSame(2, $article->readingTimeMinutes());
    }

    public function test_reading_time_counts_non_latin_scripts(): void
    {
        $article = Article::factory()->create(['body' => str_repeat('ቃል ', 600)]);

        $this->assertSame(3, $article->readingTimeMinutes());
    }

    public function test_closing_date_is_stored_as_the_end_of_the_nairobi_day(): void
    {
        $closing = NairobiTime::endOfDayUtc('2026-11-15');

        $this->assertSame('2026-11-15 20:59:59', $closing->toDateTimeString());
        $this->assertSame('2026-11-15 23:59:59', $closing->setTimezone('Africa/Nairobi')->toDateTimeString());
    }

    public function test_timestamps_are_stored_in_utc_and_displayed_in_nairobi(): void
    {
        $job = JobListing::factory()->published()->create([
            'published_at' => NairobiTime::toUtc('2026-11-01 09:00'),
        ]);

        $stored = DB::table('job_listings')->where('id', $job->id)->value('published_at');

        $this->assertSame('2026-11-01 06:00:00', Carbon::parse($stored, 'UTC')->toDateTimeString());
        $this->assertSame('09:00', NairobiTime::toNairobi($job->fresh()->published_at)->format('H:i'));
    }
}
