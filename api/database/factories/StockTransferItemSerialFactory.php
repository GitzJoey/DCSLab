<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransferItemSerialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'serial' => fake()->numerify(),
        ];
    }
}
