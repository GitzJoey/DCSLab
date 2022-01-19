<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Models\Product;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Brand;
use App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        return [
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $faker->sentence($nbWords = 6, $variableNbWords = true),
            'taxable_supply' => $faker->numberBetween(0, 1),
            'standard_rate_supply' => $faker->numberBetween(1, 10),
            'price_include_vat' => $faker->numberBetween(0, 1),
            'remarks' => $faker->word(),
            'point' => 0,
            'use_serial_number' => $faker->numberBetween(0, 1),
            'has_expiry_date' => $faker->numberBetween(0, 1),
            'product_type' => $faker->numberBetween(1, 3),
            'status' => 1
        ];
    }
}
