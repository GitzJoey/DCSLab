<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Models\ProductGroup;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->word(),
            'category' => $this->faker->numberBetween(1, 3),
        ];
    }
}
