<?php

namespace Tests\Feature;

use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use App\Models\User;
use App\Support\ImageDescriptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ImageDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_images_without_a_description_are_reported(): void
    {
        $html = '<p>Text</p>'
            .'<img src="/storage/articles/inline/a.jpg">'
            .'<img src="/storage/articles/inline/b.jpg" alt="A whiteboard of interview questions">';

        $missing = ImageDescriptions::missing($html);

        $this->assertCount(1, $missing);
        $this->assertSame('/storage/articles/inline/a.jpg', $missing[0]['src']);
    }

    public function test_descriptions_are_written_back_into_the_matching_image(): void
    {
        $html = '<p>Text</p>'
            .'<img src="/storage/articles/inline/a.jpg">'
            .'<img src="/storage/articles/inline/b.jpg" alt="Already described">';

        $result = ImageDescriptions::apply($html, [
            ['src' => '/storage/articles/inline/a.jpg', 'alt' => 'A laptop beside a notebook'],
        ]);

        $this->assertStringContainsString('src="/storage/articles/inline/a.jpg" alt="A laptop beside a notebook"', $result);
        $this->assertStringContainsString('alt="Already described"', $result);
        $this->assertSame([], ImageDescriptions::missing($result));
    }

    public function test_descriptions_are_escaped_before_they_are_stored(): void
    {
        $html = '<img src="/storage/articles/inline/a.jpg">';

        $result = ImageDescriptions::apply($html, [
            ['src' => '/storage/articles/inline/a.jpg', 'alt' => 'Say "hello" & <goodbye>'],
        ]);

        $this->assertStringNotContainsString('<goodbye>', $result);
        $this->assertStringContainsString('&quot;hello&quot;', $result);
        $this->assertStringContainsString('&amp;', $result);
    }

    public function test_an_empty_description_leaves_the_image_untouched(): void
    {
        $html = '<img src="/storage/articles/inline/a.jpg">';

        $this->assertSame($html, ImageDescriptions::apply($html, [
            ['src' => '/storage/articles/inline/a.jpg', 'alt' => '   '],
        ]));
    }

    public function test_the_editor_offers_to_describe_undescribed_images(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $withImage = Article::factory()->create([
            'body' => '<p>Body</p><img src="/storage/articles/inline/photo.jpg">',
        ]);

        $described = Article::factory()->create([
            'body' => '<p>Body</p><img src="/storage/articles/inline/photo.jpg" alt="A person at a desk">',
        ]);

        $this->actingAs($admin)->get('/mdosi/articles/'.$withImage->slug.'/edit')
            ->assertOk()
            ->assertSee('Describe images');

        $this->actingAs($admin)->get('/mdosi/articles/'.$described->slug.'/edit')
            ->assertOk()
            ->assertDontSee('Describe images');
    }

    public function test_describing_an_image_updates_the_saved_article(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);

        $article = Article::factory()->create([
            'body' => '<p>Body</p><img src="/storage/articles/inline/photo.jpg">',
        ]);

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->callAction('describeImages', data: [
                'images' => [
                    ['src' => '/storage/articles/inline/photo.jpg', 'alt' => 'A candidate at a laptop'],
                ],
            ])
            ->call('save')
            ->assertHasNoActionErrors();

        $this->assertStringContainsString(
            'alt="A candidate at a laptop"',
            $article->fresh()->body,
        );
    }
}
