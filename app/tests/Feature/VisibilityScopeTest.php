<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\JobListing;
use App\Enums\PublicationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VisibilityScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_open_scope_excludes_drafts_future_archived_and_expired_listings(): void
    {
        $open = JobListing::factory()->open()->create();
        JobListing::factory()->create();
        JobListing::factory()->published()->create(['published_at' => now()->addDay()]);
        JobListing::factory()->archived()->create();
        JobListing::factory()->closed()->create();

        $visible = JobListing::query()->open()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertSame([$open->id], $visible);
    }

    public function test_expired_listing_stays_published_but_is_not_open(): void
    {
        $expired = JobListing::factory()->closed()->create();

        $this->assertFalse($expired->isOpen());
        $this->assertTrue($expired->isClosed());
        $this->assertTrue($expired->fresh()->isPublished());

        $this->assertSame(0, JobListing::query()->open()->count());
        $this->assertSame(1, JobListing::query()->published()->count());
    }

    public function test_closing_at_boundary_is_exclusive(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-15 12:00:00', 'UTC'));

        JobListing::factory()->published()->create(['closing_at' => now()]);
        $this->assertSame(0, JobListing::query()->open()->count());

        JobListing::query()->delete();
        JobListing::factory()->published()->create(['closing_at' => now()->addSecond()]);

        $this->assertSame(1, JobListing::query()->open()->count());
    }

    public function test_article_scope_hides_drafts_scheduled_and_archived_posts(): void
    {
        $live = Article::factory()->published()->create();
        Article::factory()->create();
        Article::factory()->scheduled()->create();
        Article::factory()->archived()->create();

        $visible = Article::query()->published()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertSame([$live->id], $visible);
    }

    public function test_remote_scope_uses_work_mode_not_location(): void
    {
        $remote = JobListing::factory()->open()->remote()->create();
        JobListing::factory()->open()->create();

        $visible = JobListing::query()->open()->remote()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertSame([$remote->id], $visible);
    }

    public function test_publishing_without_a_date_stamps_the_current_time(): void
    {
        $job = JobListing::factory()->create();

        $job->update(['status' => PublicationStatus::Published]);

        $this->assertNotNull($job->fresh()->published_at);
        $this->assertTrue($job->fresh()->isPublished());
        $this->assertSame(1, JobListing::query()->open()->count());
    }

    public function test_publish_and_archive_helpers_change_visibility(): void
    {
        $job = JobListing::factory()->create();

        $this->assertFalse($job->fresh()->isPublished());

        $job->publish();

        $this->assertTrue($job->fresh()->isPublished());
        $this->assertTrue($job->fresh()->isOpen());

        $job->archive();

        $this->assertFalse($job->fresh()->isPublished());
        $this->assertSame(0, JobListing::query()->open()->count());
    }
}
