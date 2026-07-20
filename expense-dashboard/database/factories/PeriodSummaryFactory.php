<?php

namespace Database\Factories;

use App\Models\PeriodSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodSummary>
 */
class PeriodSummaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'period_start' => $this->faker->dateTimeBetween('-6 months')->format('Y-m-01'),
            'summary' => $this->faker->paragraph(),
        ];
    }
}
