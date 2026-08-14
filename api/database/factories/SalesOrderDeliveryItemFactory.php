<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderDeliveryItemFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->randomFloat(8, 1, 10000);
        $conversionValue = fake()->randomFloat(8, 1, 10000);

        return [
            'qty' => $qty,
            'product_unit_conversion_value' => $conversionValue,
            'product_unit_qty_base' => bcmul((string) $qty, (string) $conversionValue, 8),
            'remarks' => fake()->sentence(),
        ];
    }
}
