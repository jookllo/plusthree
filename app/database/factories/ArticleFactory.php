<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => fake()->unique()->sentence(6),
            'slug' => null,
            'excerpt' => fake()->sentence(18),
            'body' => fake()->paragraphs(5, true),
            'featured_image_path' => null,
            'featured_image_alt' => null,
            'status' => PublicationStatus::Draft,
            'published_at' => null,
            'seo_title' => null,
            'seo_description' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->addDays(2),
        ]);
    }

    public function archived(): static
    {
        return $this->published()->state(fn (): array => [
            'status' => PublicationStatus::Archived,
        ]);
    }
}
