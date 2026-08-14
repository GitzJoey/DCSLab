<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReturnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'global_discount' => 0,
            'rounding' => 0,
            'remarks' => fake()->sentence(),
            'is_posted' => fake()->boolean(),
        ];
    }
}
