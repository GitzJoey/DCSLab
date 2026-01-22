<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Vinkla\Hashids\Facades\Hashids;

class ProductUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'product_id' => \App\Models\Product::factory(),
            'unit_id' => \App\Models\Unit::factory(),
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'is_manufacturer_sku' => fake()->boolean(),
            'is_base' => fake()->boolean(),
            'conversion_value' => fake()->numberBetween(1, 1000),
            'is_primary_unit' => fake()->boolean(),
            'point' => fake()->numberBetween(0, 100),
            'remarks' => fake()->sentence(),
        ];
    }

    public function withRelation(bool $encode)
    {
        return $this->state(function (array $attributes) use ($encode) {
            $product = Product::inRandomOrder()->first();
            $unit = Unit::inRandomOrder()->first();

            return [
                'product_id' => $encode ? Hashids::encode($product->id) : $product->id,
                'unit_id' => $encode ? Hashids::encode($unit->id) : $unit->id,
            ];
        });
    }

    public function setIsManufacturerSku(?bool $isManufacturerSku = null)
    {
        return $this->state(function (array $attributes) use ($isManufacturerSku) {
            return [
                'is_manufacturer_sku' => is_null($isManufacturerSku) ? true : $isManufacturerSku,
            ];
        });
    }

    public function setIsBase(?bool $isBase = null)
    {
        return $this->state(function (array $attributes) use ($isBase) {
            return [
                'is_base' => is_null($isBase) ? true : $isBase,
            ];
        });
    }

    public function setIsPrimaryUnit(?bool $isPrimaryUnit = null)
    {
        return $this->state(function (array $attributes) use ($isPrimaryUnit) {
            return [
                'is_primary_unit' => is_null($isPrimaryUnit) ? false : $isPrimaryUnit,
            ];
        });
    }

    public function setConversionValue($val)
    {
        return $this->state(function (array $attributes) use ($val) {
            return [
                'conversion_value' => $val,
            ];
        });
    }
}
