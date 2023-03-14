<?php

namespace Database\Factories;

use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductUnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $locale = 'id_ID';

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'is_base' => fake()->boolean(),
            'conversion_value' => fake()->numberBetween(1, 1000),
            'is_primary_unit' => fake()->boolean(),
            'remarks' => fake($locale)->sentence(),
        ];
    }

    public function setIsBase(bool $isBase = null)
    {
        return $this->state(function (array $attributes) use ($isBase) {
            return [
                'is_base' => is_null($isBase) ? true : $isBase,
            ];
        });
    }

    public function setIsPrimaryUnit(bool $isPrimaryUnit = null)
    {
        return $this->state(function (array $attributes) use ($isPrimaryUnit) {
            return [
                'is_primary_unit' => is_null($isPrimaryUnit) ? true : $isPrimaryUnit,
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
