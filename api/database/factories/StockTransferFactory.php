<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'remarks' => fake()->sentence(),
            'is_posted' => fake()->boolean(),
        ];
    }
}
