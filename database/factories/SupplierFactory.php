<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
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

        $array_term = [
            'PIA',
            'NET',
            'EOM',
            'COD',
            'CND'
        ];

        shuffle($array_term);

        return [
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $faker->company(),
            'payment_term_type' => $array_term[0],
            'payment_term' => $array_term[0] == 'NET' ? (new RandomGenerator())->generateNumber(1, 30) : 0,
            'contact' => $faker->e164PhoneNumber(),
            'address' => $faker->address(),
            'city' => $faker->city(),
            'taxable_enterprise' => $faker->numberBetween(0, 1),
            'tax_id' => $faker->creditCardNumber(),
            'remarks' => $faker->word(),
            'status' => ActiveStatus::ACTIVE
        ];
    }
}
