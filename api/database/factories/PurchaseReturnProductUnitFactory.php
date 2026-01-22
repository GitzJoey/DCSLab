<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseReturnProductUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'qty' => fake()->numberBetween(1, 10),
            'product_unit_amount_per_unit' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_amount_total' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_initial_price' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_discount_rate1' => fake()->numberBetween(1, 100),
            'product_unit_discount_rate2' => fake()->numberBetween(1, 100),
            'product_unit_discount_rate3' => fake()->numberBetween(1, 100),
            'product_unit_discount_rate4' => fake()->numberBetween(1, 100),
            'product_unit_discount_rate5' => fake()->numberBetween(1, 100),
            'product_unit_discount_fixed1' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_discount_fixed2' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_discount_fixed3' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_discount_fixed4' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_discount_fixed5' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_net_price' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_subtotal' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_subtotal_discount_rate' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_subtotal_discount_fixed' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_total' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_global_discount_rate' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_global_discount_fixed' => fake()->numberBetween(1, 100 * 10000),
            'product_unit_grand_total' => fake()->numberBetween(1, 100 * 10000),

            'product_is_taxable' => fake()->boolean(),
            'product_vat_rate' => fake()->numberBetween(1, 100 * 10000),
            'product_price_include_vat' => fake()->boolean(),
            'product_vat_base' => fake()->numberBetween(1, 100 * 10000),
            'product_vat' => fake()->numberBetween(1, 100 * 10000),

            'product_base_unit_final_price' => fake()->numberBetween(1, 100 * 10000),

            'is_sent' => fake()->boolean(),
            'is_valid' => fake()->boolean(),
        ];
    }
}
