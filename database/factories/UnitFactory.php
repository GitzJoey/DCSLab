<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Actions\RandomGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{    
    protected $unit = [
        "PCS", "CC", "STRIP", "BOTOL", "GR", "PACK", "BOX", "ROLL", "KG", "LITER", "KALENG", "BUTIR", "IKAT", "SISIR", "JERIGEN", "BUAH", "IKT", "GALON", "PASANG", "M", "SACHET", "BKS",
    ];
    
    public function definition()
    {
        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->randomElement($this->unit),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(ProductCategory::toArrayValue())
        ];
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
        $text = $this->faker->randomElement($this->unit);

        return substr_replace($text, $str, strlen($text), 0);
    }
}
