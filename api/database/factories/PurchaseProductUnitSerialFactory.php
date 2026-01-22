<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseProductUnitSerialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'serial' => fake()->ean13(),
        ];
    }
}
