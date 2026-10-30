<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\DistributionReport;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DistributionReport>
 */
class DistributionReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'LP-'.now()->year.'-'.fake()->unique()->numerify('####'),
            'distributor_id' => Distributor::factory(),
            'market_id' => Market::factory(),
            'report_date' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            'cartons' => fake()->numberBetween(100, 600),
            'status' => fake()->randomElement(ReportStatus::cases()),
        ];
    }
}
