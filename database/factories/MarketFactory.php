<?php

namespace Database\Factories;

use App\Models\Market;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Market>
 */
class MarketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Pasar '.fake()->unique()->words(2, true),
            'lokasi' => 'Jln. '.fake()->streetName(),
            'is_active' => true,
        ];
    }
}
