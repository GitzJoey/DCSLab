<?php

namespace Database\Factories;

use App\Enums\ActiveStatus;
use App\Models\Branch;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');
        $branch_name = $faker->city();

        return [
            'code' => $faker->numberBetween(01, 10),
            'name' => $branch_name,
            'address' => $faker->address(),
            'city' => $branch_name,
            'contact' => $faker->e164PhoneNumber(),
            'remarks' => ''
        ];
    }

    public function setStatusActive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ActiveStatus::ACTIVE
            ];
        });
    }

    public function setStatusInactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => ActiveStatus::INACTIVE
            ];
        });
    }
}
