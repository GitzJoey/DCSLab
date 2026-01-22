<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleReceiptProductUnitSerialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'serial' => fake()->numerify(),
        ];
    }
}
