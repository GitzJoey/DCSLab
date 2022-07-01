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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $unit = [
            "PCS",
            "CC",
            "STRIP",
            "BOTOL",
            "GR",
            "PACK",
            "BOX",
            "ROLL",
            "KG",
            "LITER",
            "KALENG",
            "BUTIR",
            "IKAT",
            "SISIR",
            "JERIGEN",
            "BUAH",
            "IKT",
            "GALON",
            "PASANG",
            "M",
            "SACHET",
            "BKS"            
        ];

        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->randomElement($unit),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(ProductCategory::toArrayValue())
        ];
    }
}
