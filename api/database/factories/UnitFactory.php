<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
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
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->randomElement($this->units),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement($unitCategory),
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

    public function setCategoryToProductAndService()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => UnitCategory::PRODUCTS_AND_SERVICES,
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
        $text = $this->faker->randomElement($this->units);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
