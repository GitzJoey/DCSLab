<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

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
            'name' => $faker->company(),
            'status' => 1,
        ];
    }
}
