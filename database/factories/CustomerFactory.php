<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

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
            'customer_group_id' => CustomerGroup::select('id')->inRandomOrder()->limit(1)->get()[0],
            'code' => $faker->randomDigit(),
            'name' => $faker->sentence($nbWords = 6, $variableNbWords = true),
            'is_member' => $faker->numberBetween(0, 1),
            'name' => $faker->name(),
            'zone' => $faker->city(),
            'max_open_invoice' => $faker->numberBetween(0, 100),
            'max_outstanding_invoice' => $faker->numberBetween(0, 100),
            'max_invoice_age' => $faker->numberBetween(0, 100),
            'payment_term' => $faker->numberBetween(0, 1000),
            'tax_id' => $faker->numberBetween(0, 1000),
            'remarks' => '',
            'status' => 1
        ];
    }
}
