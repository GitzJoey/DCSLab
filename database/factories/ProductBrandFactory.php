<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Models\ProductBrand;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductBrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductBrand::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->word()
        ];
    }
}
