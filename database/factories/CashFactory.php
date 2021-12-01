<?php

namespace Database\Factories;

use App\Models\Cash;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cash::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        $cashes = [
            'BCA', 
            'CIMB',
            'Mandiri', 
            'Amex',
            'Cash', 
        ];
        
        return [
            'company_id' => Company::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $this->faker->numberBetween(01, 10),
            'name' => $faker->randomElement($cashes),
            'is_bank' => '1',
            'status' => '1',
        ];
    }
}
