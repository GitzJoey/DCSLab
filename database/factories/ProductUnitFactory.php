<?php

namespace Database\Factories;

use App\Models\ProductUnit;
use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;


use Illuminate\Database\Eloquent\Factories\Factory;

class ProductUnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        return [
            'company_id' => Company::select('id')->inRandomOrder()->limit(1)->get()[0],
            'product_id' => Product::select('id')->inRandomOrder()->limit(1)->get()[0],
            'unit_id' => Unit::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $faker->randomDigit(),
            'is_base' => '1',
            'conversion_value' => '1',
            'is_primary_unit' => '1',
            'remarks' => '',
        ];
    }
}
