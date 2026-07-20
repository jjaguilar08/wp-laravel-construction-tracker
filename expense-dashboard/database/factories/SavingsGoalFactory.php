<?php

namespace Database\Factories;

use App\Models\SavingsGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsGoal>
 */
class SavingsGoalFactory extends Factory
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
            'target_amount' => $this->faker->randomFloat(2, 100, 2000),
        ];
    }
}
