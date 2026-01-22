<?php

namespace Database\Factories;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement(['Unilever', 'Samsung ', 'Huawei']),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'payment_term_type' => fake()->randomElement(PaymentTermTypeEnum::toArrayEnum()),
            'payment_term' => fake()->numberBetween(1, 30),
            'taxable_enterprise' => fake()->boolean(),
            'tax_id' => fake()->numberBetween(100000000000, 999999999999),
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
        $text = fake()->randomElement(['Unilever', 'Samsung ', 'Huawei']);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
