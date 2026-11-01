<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ArticleBodyTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatting_written_in_the_editor_is_kept(): void
    {
        $article = Article::factory()->published()->create([
            'body' => '<p>Ask for <strong>specifics</strong>, not <em>generalities</em>.</p>'
                .'<h2>What to say</h2><ul><li>Name the role</li><li>Name the outcome</li></ul>',
        ]);

        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertSee('<strong>specifics</strong>', false)
            ->assertSee('<em>generalities</em>', false)
            ->assertSee('<h2>What to say</h2>', false)
            ->assertSee('<li>Name the role</li>', false);
    }

    public function test_scripts_and_event_handlers_never_reach_the_page(): void
    {
        $article = Article::factory()->published()->create([
            'body' => '<p>Before</p><script>alert(1)</script>'
                .'<img src="/storage/articles/inline/photo.jpg" onerror="alert(2)">'
                .'<p onclick="alert(3)">After</p>',
        ]);

        $response = $this->get('/blog/'.$article->slug);

        $response->assertOk()
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('alert(2)', false)
            ->assertDontSee('alert(3)', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('onclick', false)
            ->assertSee('Before')
            ->assertSee('After');
    }

    public function test_unsafe_link_targets_are_dropped_but_safe_ones_survive(): void
    {
        $article = Article::factory()->published()->create([
            'body' => '<p><a href="javascript:alert(1)">bad</a>'
                .'<a href="https://example.com/apply">good</a>'
                .'<a href="/jobs">internal</a></p>',
        ]);

        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertDontSee('javascript:', false)
            ->assertSee('href="https://example.com/apply"', false)
            ->assertSee('href="/jobs"', false);
    }

    public function test_inline_images_from_the_editor_render(): void
    {
        $article = Article::factory()->published()->create([
            'body' => '<p>Context above.</p><img src="/storage/articles/inline/diagram.png" alt="A process diagram">',
        ]);

        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertSee('src="/storage/articles/inline/diagram.png"', false)
            ->assertSee('alt="A process diagram"', false);
    }

    public function test_plain_text_bodies_are_escaped_and_keep_their_line_breaks(): void
    {
        $article = Article::factory()->published()->create([
            'body' => "First line\nSecond line <not a tag>",
        ]);

        $this->get('/blog/'.$article->slug)->assertOk()
            ->assertSee('First line<br', false)
            ->assertSee('Second line &lt;not a tag&gt;', false);
    }

    public function test_drafts_are_visible_through_a_signed_preview_link_only(): void
    {
        $article = Article::factory()->create(['title' => 'Unfinished draft', 'body' => '<p>Work in progress</p>']);

        $this->get('/blog/'.$article->slug)->assertNotFound();
        $this->get('/blog/'.$article->slug.'/preview')->assertForbidden();

        $signed = URL::temporarySignedRoute('blog.preview', now()->addMinutes(30), ['slug' => $article->slug]);

        $this->get($signed)->assertOk()
            ->assertSee('Unfinished draft')
            ->assertSee('Preview — this article is not published', false);
    }

    public function test_expired_preview_links_are_rejected(): void
    {
        $article = Article::factory()->create();

        $expired = URL::temporarySignedRoute('blog.preview', now()->subMinute(), ['slug' => $article->slug]);

        $this->get($expired)->assertForbidden();
    }

    public function test_reading_time_ignores_markup(): void
    {
        $article = Article::factory()->make([
            'body' => '<p>'.str_repeat('<strong>word</strong> ', 400).'</p>',
        ]);

        $this->assertSame(2, $article->readingTimeMinutes());
    }
}
