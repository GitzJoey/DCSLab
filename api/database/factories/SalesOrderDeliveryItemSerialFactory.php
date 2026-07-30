<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderDeliveryItemSerialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'serial' => fake()->numerify(),
        ];
    }
}
