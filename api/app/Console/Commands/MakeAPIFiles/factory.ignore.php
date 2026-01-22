<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RepToPascalThisFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'remarks' => fake()->sentence(),
        ];
    }
}
