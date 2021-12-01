<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        $brands = [
            'Sampoerna', 
            'Bimoli',
            'Fortune', 
            'LA',
            'Tropical', 
            'Chitato',
            'Kriket', 
            'Tokai',
            'Aqua',
            'Indomie',
            'Cleo',
            'Peperro',
            'Segitiga Biru',
        ];

        return [
            'company_id' => Company::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $this->faker->numberBetween(01, 10),
            'name' => $faker->randomElement($brands),
        ];
    }
}
