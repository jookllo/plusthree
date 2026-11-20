<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The "Keep reading" block at the foot of a post. It used to depend entirely
 * on shared categories, so an unfiled article showed nothing at all.
 */
class RelatedArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_post_without_a_category_still_offers_other_posts(): void
    {
        $current = Article::factory()->published()->create(['title' => 'The one being read']);

        Article::factory()->published()->create(['title' => 'Second piece']);
        Article::factory()->published()->create(['title' => 'Third piece']);

        $related = $this->relatedSection($current);

        $this->assertStringContainsString('Keep reading', $related);
        $this->assertStringContainsString('Second piece', $related);
        $this->assertStringContainsString('Third piece', $related);
        $this->assertStringNotContainsString('The one being read', $related);
    }

    public function test_the_block_holds_at_most_three_posts(): void
    {
        $current = Article::factory()->published()->create(['title' => 'The one being read']);

        Article::factory()->published()->count(6)->create();

        $related = $this->relatedSection($current);

        $this->assertSame(3, substr_count($related, 'class="article-card"'));
    }

    public function test_posts_sharing_a_category_come_first(): void
    {
        $sector = Category::factory()->article()->create(['name' => 'Interviews']);

        $current = Article::factory()->published()->create([
            'title' => 'The one being read',
            'published_at' => now()->subDay(),
        ]);
        $current->categories()->attach($sector);

        // Newer, but unrelated.
        Article::factory()->published()->create([
            'title' => 'Newer unrelated piece',
            'published_at' => now(),
        ]);

        $sameTopic = Article::factory()->published()->create([
            'title' => 'Older piece on the same topic',
            'published_at' => now()->subWeek(),
        ]);
        $sameTopic->categories()->attach($sector);

        $related = $this->relatedSection($current);

        $this->assertLessThan(
            strpos($related, 'Newer unrelated piece'),
            strpos($related, 'Older piece on the same topic'),
            'A shared category outranks being recent.',
        );
    }

    public function test_drafts_and_future_posts_are_never_offered(): void
    {
        $current = Article::factory()->published()->create(['title' => 'The one being read']);

        Article::factory()->create(['title' => 'Unpublished draft']);
        Article::factory()->scheduled()->create(['title' => 'Not out yet']);
        Article::factory()->published()->create(['title' => 'A published piece']);

        $related = $this->relatedSection($current);

        $this->assertStringContainsString('A published piece', $related);
        $this->assertStringNotContainsString('Unpublished draft', $related);
        $this->assertStringNotContainsString('Not out yet', $related);
    }

    /**
     * Only the "Keep reading" section, so assertions cannot match the title in
     * the hero or the metadata.
     */
    private function relatedSection(Article $article): string
    {
        $html = $this->get('/blog/'.$article->slug)->assertOk()->getContent();

        return Str::between($html, 'id="related-heading"', '</section>');
    }
}
