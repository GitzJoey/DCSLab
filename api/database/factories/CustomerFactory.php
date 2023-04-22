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
        $faker = \Faker\Factory::create('id_ID');
        $rand = new RandomizerActions();

        return [
            'code' => $rand->generateAlpha().$rand->generateNumeric(),
            'is_member' => $this->faker->boolean(),
            'name' => $this->faker->name(),
            'zone' => $faker->city(),
            'max_open_invoice' => $faker->numberBetween(0, 100),
            'max_outstanding_invoice' => $faker->numberBetween(0, 100000000),
            'max_invoice_age' => $faker->numberBetween(0, 366),
            'payment_term_type' => $faker->randomElement(PaymentTermType::toArrayEnum()),
            'payment_term' => $faker->numberBetween(0, 366),
            'taxable_enterprise' => $this->faker->boolean(),
            'tax_id' => $faker->numberBetween(0, 100000000),
            'remarks' => $faker->sentence(),
            'status' => $faker->randomElement(RecordStatus::toArrayEnum()),
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
