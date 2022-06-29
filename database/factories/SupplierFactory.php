<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Enums\PaymentTermType;
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

        $array_term = $faker->randomElement(PaymentTermType::toArrayValue());
        $status = $faker->randomElement(RecordStatus::toArrayValue());

        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $faker->company(),
            'payment_term_type' => $array_term,
            'payment_term' => $array_term == 'NET' ? (new RandomGenerator())->generateNumber(1, 30) : 0,
            'contact' => $faker->e164PhoneNumber(),
            'address' => $faker->address(),
            'city' => $faker->city(),
            'taxable_enterprise' => $faker->boolean(),
            'tax_id' => $faker->creditCardNumber(),
            'remarks' => $faker->word(),
            'status' => $status
        ];
    }
}
