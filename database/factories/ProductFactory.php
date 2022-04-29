<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
use App\Enums\ProductType;
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

        $product_type = $faker->randomElement(ProductType::toArrayValue());
        $status = $faker->randomElement(ActiveStatus::toArrayValue());

        return [
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $faker->sentence($nbWords = 6, $variableNbWords = true),
            'taxable_supply' => (new RandomGenerator())->randomTrueOrFalse(),
            'standard_rated_supply' => $faker->numberBetween(1, 10),
            'price_include_vat' => (new RandomGenerator())->randomTrueOrFalse(),
            'remarks' => $faker->word(),
            'point' => $faker->numberBetween(1, 100),
            'product_type' => $product_type,
            'use_serial_number' => (new RandomGenerator())->randomTrueOrFalse(),
            'has_expiry_date' => (new RandomGenerator())->randomTrueOrFalse(),
            'status' => $status
        ];
    }
}
