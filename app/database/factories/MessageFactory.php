<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\MessageStatus;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'company' => fake()->company(),
            'role_title' => fake()->jobTitle(),
            'employment_type' => EmploymentType::FullTime,
            'link' => 'https://example.com/jobs/role',
            'body' => fake()->paragraph(3),
            'source' => 'employer_form',
            'status' => MessageStatus::New,
            'handled_at' => null,
        ];
    }

    public function handled(): static
    {
        return $this->state(fn (): array => [
            'status' => MessageStatus::Handled,
            'handled_at' => now(),
        ]);
    }
}
