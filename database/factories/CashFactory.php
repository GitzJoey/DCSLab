<?php

namespace Database\Factories;

use App\Models\Cash;
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
        return [
            'code' => $this->faker->numberBetween(01, 10),
            'name' => $this->faker->creditCardType(),
            'is_bank' => '1',
            'status' => '1',
        ];
    }
}
