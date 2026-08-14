<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'amount' => fake()->randomFloat(8, 1, 1000000),
            'amount_allocated' => 0,
            'remarks' => fake()->sentence(),
        ];
    }
}
