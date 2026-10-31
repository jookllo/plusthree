<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kind' => Category::KIND_JOB,
            'name' => fake()->unique()->words(2, true),
            'slug' => null,
        ];
    }

    public function job(): static
    {
        return $this->state(fn (): array => ['kind' => Category::KIND_JOB]);
    }

    public function article(): static
    {
        return $this->state(fn (): array => ['kind' => Category::KIND_ARTICLE]);
    }
}
