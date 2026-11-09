<?php

namespace Database\Factories;

use App\Enums\DistributionStatus;
use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Distribution>
 */
class DistributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'distributor_id' => Distributor::factory(),
            'market_id' => Market::factory(),
            'uptd_user_id' => User::factory()->state(['role' => Role::UptdPasar]),
            'distribution_date' => now()->toDateString(),
            'cartons' => fake()->numberBetween(50, 500),
            'status' => DistributionStatus::Submitted,
        ];
    }

    /**
     * Indicate that the report is still a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DistributionStatus::Draft,
        ]);
    }
}
