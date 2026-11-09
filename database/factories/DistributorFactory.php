<?php

namespace Database\Factories;

use App\Models\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Distributor>
 */
class DistributorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'PT '.fake()->company(),
            'wilayah' => 'Padang',
            'penanggung_jawab' => fake()->name(),
            'telepon' => '0751'.fake()->numerify('######'),
            'alamat_kantor' => fake()->streetAddress().', Padang',
            'is_active' => true,
        ];
    }
}
