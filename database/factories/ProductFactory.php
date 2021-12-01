<?php

namespace Database\Factories;

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
            'company_id' => Company::select('id')->inRandomOrder()->limit(1)->get()[0],
            'group_id' => ProductGroup::select('id')->inRandomOrder()->limit(1)->get()[0],
            'brand_id' => Brand::select('id')->inRandomOrder()->limit(1)->get()[0],
            'supplier_id' => Supplier::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $faker->randomDigit(),
            'name' => $faker->sentence($nbWords = 6, $variableNbWords = true),
            'tax_status' => $faker->numberBetween(1, 3),
            'remarks' => $faker->word(),
            'point' => 0,
            'is_use_serial' => $faker->numberBetween(0, 1),
            'product_type' => $faker->numberBetween(1, 3),
            'status' => 1
            
        ];
    }
}