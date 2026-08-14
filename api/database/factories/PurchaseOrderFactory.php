<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'due_days' => fake()->numberBetween(0, 60),
            'remarks' => fake()->sentence(),
        ];
    }
}
