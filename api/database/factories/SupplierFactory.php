<?php

namespace Database\Factories;

use App\Enums\PaymentTermType;
use App\Enums\RecordStatus;
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
        $array_term = fake()->randomElement(PaymentTermType::toArrayEnum());
        $status = fake()->randomElement(RecordStatus::toArrayEnum());

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake('id_ID')->company(),
            'payment_term_type' => $array_term,
            'payment_term' => $array_term == 'NET' ? fake()->numberBetween(1, 30) : 0,
            'contact' => fake()->e164PhoneNumber(),
            'address' => fake('id_ID')->address(),
            'city' => fake('id_ID')->city(),
            'taxable_enterprise' => fake()->boolean(),
            'tax_id' => fake()->creditCardNumber(),
            'remarks' => fake()->word(),
            'status' => $status,
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
        $text = fake('id_ID')->company();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
