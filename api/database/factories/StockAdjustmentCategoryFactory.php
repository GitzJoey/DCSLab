<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StockAdjustmentCategoryFactory extends Factory
{
    public function definition(): array
    {
        $names = [
            'Koreksi Pencatatan',
            'Stock Opname',
            'Hilang',
            'Rusak',
        ];

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement($names).Str::random(3),
        ];
    }
}
