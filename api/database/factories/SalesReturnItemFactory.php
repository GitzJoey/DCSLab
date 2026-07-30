<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReturnItemFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->randomFloat(8, 1, 10000);
        $conversionValue = fake()->randomFloat(8, 1, 10000);

        return [
            'qty' => $qty,
            'product_unit_conversion_value' => $conversionValue,
            'product_unit_qty_base' => bcmul((string) $qty, (string) $conversionValue, 8),
            'product_unit_price' => fake()->randomFloat(8, 1, 1000000),
            'product_unit_is_price_include_vat' => fake()->boolean(),
            'price_discount' => 0,
            'subtotal_discount' => 0,
            'vat_rate' => 0,
            'vat_base_numerator' => 1,
            'vat_base_denominator' => 1,
            'remarks' => fake()->sentence(),
        ];
    }
}
