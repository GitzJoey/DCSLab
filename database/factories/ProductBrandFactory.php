<?php

namespace Database\Factories;

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
            'company_id' => Company::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $this->faker->numberBetween(01, 10),
            'name' => $this->faker->word(),
        ];
    }
}
