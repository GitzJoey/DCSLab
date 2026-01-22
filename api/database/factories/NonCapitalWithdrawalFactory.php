<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NonCapitalWithdrawalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'amount' => fake()->numberBetween(0, 1000000),
            'remarks' => fake()->sentence(),
        ];
    }
}
