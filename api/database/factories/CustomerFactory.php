<?php

namespace Database\Factories;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).' '.fake()->numerify(),
            'is_member' => fake()->boolean(),
            'name' => fake()->name(),
            'zone' => fake()->city(),
            'max_open_invoice' => fake()->randomNumber(),
            'max_outstanding_invoice' => fake()->randomNumber(),
            'max_invoice_age' => fake()->randomNumber(),
            'payment_term_type' => fake()->randomElement(PaymentTermTypeEnum::toArrayEnum()),
            'payment_term' => fake()->randomNumber(),
            'taxable_enterprise' => fake()->boolean(),
            'tax_id' => (string) fake()->numberBetween(100000000000, 999999999999),
            'status' => fake()->randomElement(RecordStatusEnum::toArrayEnum()),
            'remarks' => fake()->sentence(),
        ];
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
        $text = fake()->name();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
