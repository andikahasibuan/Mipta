<?php

namespace Database\Factories;

use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipientStore>
 */
class RecipientStoreFactory extends Factory
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
            'name' => 'Toko '.fake()->unique()->words(2, true),
            'alamat' => fake()->streetAddress(),
            'nib' => fake()->numerify('#############'),
            'is_active' => true,
        ];
    }
}
