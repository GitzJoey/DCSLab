<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransferProductUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'qty' => fake()->randomNumber(0, 10000),
            'product_unit_amount_per_unit' => fake()->randomNumber(0, 10000),
            'product_unit_amount_total' => fake()->randomNumber(0, 10000),
        ];
    }
}
