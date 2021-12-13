<?php

namespace Database\Factories;

use App\Models\Supplier;

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
            'company_id' => '',
            'code' => $faker->unique()->numberBetween(001, 99999),
            'name' => $faker->name(),
            'payment_term_type' => $faker->numberBetween(0, 28),
            'contact' => $faker->e164PhoneNumber(),
            'address' => $faker->address(),
            'city' => $faker->city(),
            'taxable_enterprise' => $faker->numberBetween(0, 1),
            'tax_id' => $faker->creditCardNumber(),
            'remarks' => $faker->word(),
            'status' => '1'
        ];
    }
}
