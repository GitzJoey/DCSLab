<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Unit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        $units = [
            'GR', 
            'ONS',
            'KG', 
            'MTR',
            'PCS', 
            'PACK',
            'SAK', 
            'KRT',
            'BKS',
            'SLOP',
            'DUS',
            'ROLL',
            'BTG',
        ];
        
        return [
            'company_id' => Company::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $faker->numberBetween(01, 10),
            'name' => $faker->randomElement($units),
        ];
    }
}
