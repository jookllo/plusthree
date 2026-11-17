<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'starts_at' => now()->addDays(5)->setTime(18, 0),
            'location' => 'Nairobi, Kenya',
            'description' => fake()->sentence(14),
            'url' => 'https://example.org/events/'.fake()->unique()->slug(2),
            'published_at' => now(),
        ];
    }

    /**
     * An event whose date has already passed.
     */
    public function past(): static
    {
        return $this->state(fn (): array => [
            'starts_at' => now()->subDays(3)->setTime(18, 0),
        ]);
    }

    public function online(): static
    {
        return $this->state(fn (): array => ['location' => 'Online']);
    }
}
