<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 1, 500),
            'category' => $this->faker->randomElement(Expense::CATEGORIES),
            'date' => $this->faker->dateTimeBetween('-6 months')->format('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
