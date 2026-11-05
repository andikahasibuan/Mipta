<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketMonitoring>
 */
class MarketMonitoringFactory extends Factory
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
            'uptd_user_id' => User::factory()->state(['role' => Role::UptdPasar]),
            'monitored_at' => now()->toDateString(),
        ];
    }
}
