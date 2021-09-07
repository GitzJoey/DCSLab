<?php

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->numberBetween(01, 10),
            'name' => $this->faker->name(),
            'is_member_card' => '1',
            'use_limit_outstanding_notes' => '1',
            'limit_outstanding_notes' => $this->faker->randomDigit(),
            'use_limit_payable_nominal' => '1',
            'limit_payable_nominal' => $this->faker->randomDigit(),
            'use_limit_age_notes' => '1',
            'limit_age_notes' => $this->faker->randomDigit(),
            'term' => $this->faker->randomDigit(),
            'selling_point' => $this->faker->randomDigit(),
            'selling_point_multiple' => $this->faker->randomDigit(),
            'sell_at_capital_price' => $this->faker->randomDigit(),
            'global_markup_percent' => $this->faker->randomDigit(),
            'global_discount_percent' => $this->faker->randomDigit(),
            'global_discount_nominal' => $this->faker->randomDigit(),
            'is_rounding' => '1',
            'round_on' => '1',
            'round_digit' => $this->faker->randomDigit(),
            'remarks' => '',
            'cash_id' => '',
        ];
    }
}
