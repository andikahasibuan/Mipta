<?php

namespace Database\Factories;

use App\Models\Market;
use App\Models\UptdContributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UptdContributor>
 */
class UptdContributorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'market_id' => Market::factory(),
            'name' => 'Kontributor '.fake()->unique()->words(2, true),
            'username' => 'kontributor.'.fake()->unique()->userName(),
            'password' => 'uptdpasar123',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the contributor is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
