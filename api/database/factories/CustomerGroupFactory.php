<?php

namespace Database\Factories;

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
        $locale = 'id_ID';

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement($this->customerGroups),
            'max_open_invoice' => fake()->numberBetween(0, 100),
            'max_outstanding_invoice' => fake()->randomFloat(-4, 1000, 10000000),
            'max_invoice_age' => fake()->numberBetween(0, 366),
            'payment_term_type' => fake()->randomElement(PaymentTermType::toArrayEnum()),
            'payment_term' => fake()->numberBetween(0, 366),
            'selling_point' => fake()->numberBetween(0, 1000),
            'selling_point_multiple' => fake()->randomFloat(-4, 1000, 10000000),
            'sell_at_cost' => false,
            'price_markup_percent' => fake()->numberBetween(0, 10),
            'price_markup_nominal' => fake()->randomFloat(-4, 1000, 10000000),
            'price_markdown_percent' => fake()->numberBetween(0, 100) / 100,
            'price_markdown_nominal' => fake()->randomFloat(-4, 1000, 10000000),
            'round_on' => fake()->randomElement(RoundOn::toArrayEnum()),
            'round_digit' => fake()->numberBetween(0, 6),
            'remarks' => fake($locale)->sentence(),
        ];
    }

    public function setPaymentTermTypeName()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_term_type' => fake()->randomElement(PaymentTermType::toArrayEnum()),
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
