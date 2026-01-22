<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleProductUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'qty' => fake()->randomNumber(0, 10000),
            'product_unit_amount_per_unit' => fake()->randomNumber(0, 10000),
            'product_unit_amount_total' => fake()->randomNumber(0, 10000),
            'product_unit_initial_price' => fake()->randomNumber(0, 10000),
            'product_unit_discount_rate1' => fake()->randomNumber(0, 10000),
            'product_unit_discount_rate2' => fake()->randomNumber(0, 10000),
            'product_unit_discount_rate3' => fake()->randomNumber(0, 10000),
            'product_unit_discount_rate4' => fake()->randomNumber(0, 10000),
            'product_unit_discount_rate5' => fake()->randomNumber(0, 10000),
            'product_unit_discount_fixed1' => fake()->randomNumber(0, 10000),
            'product_unit_discount_fixed2' => fake()->randomNumber(0, 10000),
            'product_unit_discount_fixed3' => fake()->randomNumber(0, 10000),
            'product_unit_discount_fixed4' => fake()->randomNumber(0, 10000),
            'product_unit_discount_fixed5' => fake()->randomNumber(0, 10000),
            'product_unit_net_price' => fake()->randomNumber(0, 10000),
            'product_unit_subtotal' => fake()->randomNumber(0, 10000),
            'product_unit_subtotal_discount_rate' => fake()->randomNumber(0, 10000),
            'product_unit_subtotal_discount_fixed' => fake()->randomNumber(0, 10000),
            'product_unit_total' => fake()->randomNumber(0, 10000),

            'product_is_taxable' => fake()->boolean(),
            'product_vat_rate' => fake()->randomNumber(0, 10000),
            'product_price_include_vat' => fake()->boolean(),
            'product_vat_base' => fake()->randomNumber(0, 10000),
            'product_vat' => fake()->randomNumber(0, 10000),

            'product_unit_final_price' => fake()->randomNumber(0, 10000),

            'is_received' => fake()->boolean(),
            'is_valid' => fake()->boolean(),
        ];
    }
}
