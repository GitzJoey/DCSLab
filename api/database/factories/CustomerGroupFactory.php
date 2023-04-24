<?php

namespace Database\Factories;

use App\Actions\Randomizer\RandomizerActions;
use App\Enums\PaymentTermType;
use App\Enums\RoundOn;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerGroup>
 */
class CustomerGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @var string
     */
    protected $model = CustomerGroup::class;

    protected $customerGroups = [
        'Retail', 'Whole Sale', 'Regular',
    ];

    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');
        $rand = new RandomizerActions();

        return [
            'code' => $rand->generateAlpha().$rand->generateNumeric(),
            'name' => $faker->randomElement($this->customerGroups),
            'max_open_invoice' => $faker->numberBetween(0, 100),
            'max_outstanding_invoice' => $faker->numberBetween(0, 100000000),
            'max_invoice_age' => $faker->numberBetween(0, 366),
            'payment_term_type' => $faker->randomElement(PaymentTermType::toArrayEnum()),
            'payment_term' => $faker->numberBetween(0, 366),
            'selling_point' => $faker->numberBetween(0, 1000),
            'selling_point_multiple' => $faker->numberBetween(0, 100000000),
            'sell_at_cost' => false,
            'price_markup_percent' => $faker->numberBetween(0, 10),
            'price_markup_nominal' => $faker->numberBetween(0, 100000000),
            'price_markdown_percent' => $faker->numberBetween(0, 1),
            'price_markdown_nominal' => $faker->numberBetween(0, 100000000),
            'round_on' => $faker->randomElement(RoundOn::toArrayEnum()),
            'round_digit' => $faker->numberBetween(0, 6),
            'remarks' => $faker->sentence(),
        ];
    }

    public function setPaymentTermTypeName()
    {
        return $this->state(function (array $attributes) {
            $faker = \Faker\Factory::create('id_ID');

            return [
                'payment_term_type' => $faker->randomElement(PaymentTermType::toArrayEnum()),
            ];
        });
    }

    public function insertStringInName(string $str)
    {
        return $this->state(function (array $attributes) use ($str) {
            return [
                'name' => $this->craftName($str),
            ];
        });
    }

    private function craftName(string $str)
    {
        $text = $this->faker->randomElement($this->customerGroups);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
