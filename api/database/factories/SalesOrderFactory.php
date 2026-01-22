<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'shipping_date' => fake()->date(),
            'remarks' => fake()->sentence(),
            'is_has_invoice' => fake()->boolean(),
            'is_received' => fake()->boolean(),
            'total' => fake()->numberBetween(10000, 1000000),
            'global_discount_rate' => fake()->numberBetween(0, 100),
            'global_discount_fixed' => fake()->numberBetween(0, 100),
            'grand_total' => fake()->numberBetween(10000, 1000000),
            'down_payment' => fake()->numberBetween(0, 1000000),
            'down_payment_due_days' => fake()->numberBetween(0, 100),
            'down_payment_applied' => fake()->numberBetween(0, 1000000),
            'down_payment_remaining' => fake()->numberBetween(0, 1000000),
            'is_down_payment_paid_off' => fake()->boolean(),
        ];
    }
}
