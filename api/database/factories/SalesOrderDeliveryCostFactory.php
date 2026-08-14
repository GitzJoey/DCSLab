<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderDeliveryCostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'name' => fake()->words(2, true),
            'amount' => fake()->randomFloat(8, 1, 1000000),
            'remarks' => fake()->sentence(),
        ];
    }
}
