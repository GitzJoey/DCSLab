<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustmentOutProduct>
 */
class StockAdjustmentOutProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->randomNumber(2);
        $conversionValue = fake()->randomFloat(2, 1, 10);
        $qtyBase = $qty * $conversionValue;

        return [
            'qty' => $qty,
            'product_unit_conversion_value' => $conversionValue,
            'product_unit_qty_base' => $qtyBase,
            'remarks' => fake()->sentence(),
        ];
    }
}
