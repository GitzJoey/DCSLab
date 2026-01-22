<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleOrderDownPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 0, 1000),
            'remarks' => fake()->sentence(),
        ];
    }
}
