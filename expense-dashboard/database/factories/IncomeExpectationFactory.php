<?php

namespace Database\Factories;

use App\Models\IncomeExpectation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncomeExpectation>
 */
class IncomeExpectationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month' => $this->faker->dateTimeBetween('-6 months')->format('Y-m-01'),
            'expected_amount' => $this->faker->randomFloat(2, 1000, 8000),
        ];
    }
}
