<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierProduct>
 */
class SupplierProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'main_product' => fake()->boolean(),
        ];
    }

    public function setMainProduct(bool $val = null)
    {
        return $this->state(function (array $attributes) use ($val) {
            return [
                'main_product' => is_null($val) ? false : $val,
            ];
        });
    }
}
