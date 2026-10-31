<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Enums\WorkMode;
use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'title' => fake()->unique()->jobTitle(),
            'slug' => null,
            'description' => fake()->paragraph(4),
            'requirements' => fake()->paragraph(3),
            'employment_type' => EmploymentType::FullTime,
            'work_mode' => WorkMode::OnSite,
            'location_city' => 'Nairobi',
            'location_country' => 'Kenya',
            'application_url' => 'https://example.com/jobs/apply',
            'application_email' => null,
            'status' => PublicationStatus::Draft,
            'published_at' => null,
            'closing_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function open(): static
    {
        return $this->published()->state(fn (): array => [
            'closing_at' => now()->addWeeks(3),
        ]);
    }

    public function closed(): static
    {
        return $this->published()->state(fn (): array => [
            'closing_at' => now()->subDay(),
        ]);
    }

    public function remote(): static
    {
        return $this->state(fn (): array => [
            'work_mode' => WorkMode::Remote,
            'location_city' => null,
            'location_country' => 'Kenya',
        ]);
    }

    public function archived(): static
    {
        return $this->published()->state(fn (): array => [
            'status' => PublicationStatus::Archived,
        ]);
    }
}
