<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Supplier::class;

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
            'code' => $faker->numberBetween(01, 10),
            'name' => $faker->name(),
            'term' => $faker->numberBetween(7, 28),
            'contact' => $faker->e164PhoneNumber(),
            'address' => $faker->address(),
            'city' => $faker->city(),
            'is_tax' => $faker->numberBetween(0, 1),
            'tax_number' => $faker->creditCardNumber(),
            'remarks' => $faker->word(),
            'status' => '1'
        ];
    }
}
