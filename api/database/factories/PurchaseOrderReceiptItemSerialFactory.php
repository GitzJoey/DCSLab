<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderReceiptItemSerialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'serial' => fake()->numerify(),
        ];
    }
}
