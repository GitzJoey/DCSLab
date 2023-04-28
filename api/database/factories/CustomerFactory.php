<?php

namespace Database\Factories;

use App\Actions\Randomizer\RandomizerActions;
use App\Enums\PaymentTermType;
use App\Enums\RecordStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @var string
     */
    protected $model = Customer::class;

    public function definition()
    {
        $locale = 'id_ID';

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'is_member' => false,
            'name' => fake($locale)->name(),
            'zone' => fake($locale)->city(),
            'max_open_invoice' => fake()->numberBetween(0, 100),
            'max_outstanding_invoice' => fake()->randomFloat(-4, 1000, 10000000),
            'max_invoice_age' => fake()->numberBetween(0, 366),
            'payment_term_type' => fake()->randomElement(PaymentTermType::toArrayEnum()),
            'payment_term' => fake()->numberBetween(0, 366),
            'taxable_enterprise' => false,
            'tax_id' => fake()->randomFloat(-4, 1000, 10000000),
            'remarks' => fake($locale)->sentence(),
            'status' => fake()->randomElement(RecordStatus::toArrayEnum()),
        ];
    }

    public function setStatusActive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::ACTIVE,
            ];
        });
    }

    public function setStatusInactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::INACTIVE,
            ];
        });
    }

    public function setIsMemberCustomer(bool $bool = true)
    {
        return $this->state(function (array $attributes) use ($bool) {
            return [
                'is_member' => $bool,
            ];
        });
    }

    public function setTaxableEnterprise(bool $bool = true)
    {
        return $this->state(function (array $attributes) use ($bool) {
            return [
                'taxable_enterprise' => $bool,
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
        $text = $this->faker->customer();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
