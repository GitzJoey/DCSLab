<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Enums\ActiveStatus;
use App\Actions\RandomGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');
        
        return [
            'name' => $faker->name,
            'email' => $faker->email,
            'address' => $faker->address,
            'city' => $faker->city,
            'postal_code' => $faker->postcode,
            'country' => $faker->country,
            'tax_id' => (new RandomGenerator())->generateNumber(10000000, 999999999),
            'ic_num' => (new RandomGenerator())->generateNumber(10000000, 999999999),
            'join_date' => $faker->date($format = 'Y-m-d', $max = 'now'),
            'remarks' => $faker->sentence,
            'status' => $this->faker->randomElement(ActiveStatus::toArrayValue())
        ];
    }
}
