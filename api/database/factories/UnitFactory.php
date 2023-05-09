<?php

namespace Database\Factories;

use App\Enums\UnitCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    protected $units = [
        'PCS', 'CC', 'STRIP', 'BOTOL', 'GR', 'PACK', 'BOX', 'ROLL', 'KG', 'LITER', 'KALENG', 'BUTIR', 'IKAT', 'SISIR', 'JERIGEN', 'BUAH', 'IKT', 'GALON', 'PASANG', 'MTR', 'SACHET', 'BKS',
    ];

    public function definition()
    {
        $unitCategory = UnitCategory::toArrayEnum();
        unset($unitCategory[2]);

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement($this->units),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement($unitCategory),
        ];
    }

    public function setCategoryToProduct()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => UnitCategory::PRODUCTS,
            ];
        });
    }

    public function setCategoryToService()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => UnitCategory::SERVICES,
            ];
        });
    }

    public function insertStringInName(string $str)
    {
        return $this->state(function (array $attributes) use ($str) {
            return [
                'name' => $this->craftName($str),
            ];
        });
    }

    private function craftName(string $str)
    {
        $text = fake()->randomElement($this->units);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
