<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
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
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $faker->company(),
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
