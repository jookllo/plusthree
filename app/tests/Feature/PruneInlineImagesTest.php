<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PruneInlineImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_it_reports_orphans_without_deleting_them_by_default(): void
    {
        $this->ageFiles(['articles/inline/used.jpg', 'articles/inline/orphan.jpg']);

        Article::factory()->create([
            'body' => '<p>Text</p><img src="/storage/articles/inline/used.jpg" alt="Used">',
        ]);

        $this->artisan('images:prune')
            ->expectsOutputToContain('Would delete articles/inline/orphan.jpg')
            ->assertSuccessful();

        Storage::disk('public')->assertExists('articles/inline/orphan.jpg');
        Storage::disk('public')->assertExists('articles/inline/used.jpg');
    }

    public function test_it_deletes_orphans_when_forced(): void
    {
        $this->ageFiles(['articles/inline/used.jpg', 'articles/inline/orphan.jpg']);

        Article::factory()->create([
            'body' => '<p>Text</p><img src="https://example.com/storage/articles/inline/used.jpg">',
        ]);

        $this->artisan('images:prune', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertMissing('articles/inline/orphan.jpg');
        Storage::disk('public')->assertExists('articles/inline/used.jpg');
    }

    public function test_recent_files_are_left_alone(): void
    {
        Storage::disk('public')->put('articles/inline/fresh.jpg', 'image');

        $this->artisan('images:prune', ['--force' => true])->assertSuccessful();

        Storage::disk('public')->assertExists('articles/inline/fresh.jpg');
    }

    /**
     * @param  list<string>  $paths
     */
    private function ageFiles(array $paths): void
    {
        foreach ($paths as $path) {
            Storage::disk('public')->put($path, 'image');
        }

        // Storage::fake records lastModified as now(); move it into the past.
        $this->travel(2)->days();
    }
}
