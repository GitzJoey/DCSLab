<?php

namespace Database\Factories;

use App\Models\CustomerGroup;
use App\Models\Cash;

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
        $cashCount = Cash::count();
        if ($cashCount == 0) {
            Cash::factory()->count(5)->create();
        }

        return [
            'code' => $this->faker->numberBetween(01, 10),
            'cash_id' => Cash::select('id')->inRandomOrder()->limit(1)->get()[0],
            'name' => $this->faker->name(),
            'is_member' => $this->faker->numberBetween(0, 1),
            'max_open_invoice' => $this->faker->randomDigit(),
            'max_outstanding_invoice' => $this->faker->randomDigit(),
            'max_invoice_age' => $this->faker->randomDigit(),
            'payment_term' => $this->faker->randomDigit(),
            'selling_point' => $this->faker->randomDigit(),
            'selling_point_multiple' => $this->faker->randomDigit(),
            'sell_at_capital_price' => $this->faker->randomDigit(),
            'price_markup_percent' => $this->faker->randomDigit(),
            'price_markdown_percent' => $this->faker->randomDigit(),
            'price_markdown_nominal' => $this->faker->randomDigit(),
            'round_on' => '1',
            'round_digit' => $this->faker->randomDigit(),
            'remarks' => '',
        ];
    }
}
