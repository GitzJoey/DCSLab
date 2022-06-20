<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Models\Unit;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Unit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $units = [
            'Pcs','Doz','Pack','Box','Kg','G','L','ML'
        ];

        return [
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->randomElement($units),
            'description' => $this->faker->sentence,
            'category' => $this->faker->numberBetween(1, 3)
        ];
    }
}
