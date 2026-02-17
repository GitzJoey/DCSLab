<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustmentInProduct>
 */
class StockAdjustmentInProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->randomNumber(2);
        $productUnitCogs = fake()->randomNumber(5);
        $conversionValue = fake()->randomFloat(2, 1, 10);
        $qtyBase = $qty * $conversionValue;
        $totalUnitCogs = $qty * $productUnitCogs;
        $baseUnitCogs = $qtyBase > 0 ? $totalUnitCogs / $qtyBase : 0;

        return [
            'qty' => $qty,
            'product_unit_conversion_value' => $conversionValue,
            'product_unit_qty_base' => $qtyBase,
            'product_unit_cogs' => $productUnitCogs,
            'product_unit_total_cogs' => $totalUnitCogs,
            'product_unit_base_unit_cogs' => $baseUnitCogs,
            'remarks' => fake()->sentence(),
        ];
    }
}
