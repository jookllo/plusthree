<?php

namespace Database\Factories;

use App\Enums\EmailStatus;
use App\Models\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Email>
 */
class EmailFactory extends Factory
{
    protected $model = Email::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(5),
            'from_address' => null,
            'body' => '<p>'.fake()->paragraph(3).'</p>',
            'recipients' => [fake()->unique()->safeEmail()],
            'status' => EmailStatus::Draft,
            'error' => null,
            'sent_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => EmailStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => EmailStatus::Failed,
            'error' => 'The provider rejected the message.',
        ]);
    }
}
