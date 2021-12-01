<?php

namespace Database\Factories;

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
        $faker = \Faker\Factory::create('id_ID');

        $groups = [
            'Rokok', 
            'Minyak',
            'Beras', 
            'Jajan',
            'Air Mineral', 
            'Korek',
            'Mie', 
            'Tepung Terigu',
        ];

        return [
            'company_id' => Company::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $this->faker->numberBetween(01, 10),
            'name' => $faker->randomElement($groups),
            'category' => $this->faker->numberBetween(1, 3),
        ];
    }
}
