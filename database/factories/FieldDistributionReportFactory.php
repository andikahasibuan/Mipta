<?php

namespace Database\Factories;

use App\Enums\FieldCondition;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\UptdContributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FieldDistributionReport>
 */
class FieldDistributionReportFactory extends Factory
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
            'uptd_contributor_id' => UptdContributor::factory(),
            'report_date' => now()->toDateString(),
            'description' => fake()->sentence(),
            'condition' => fake()->randomElement(FieldCondition::cases()),
        ];
    }
}
