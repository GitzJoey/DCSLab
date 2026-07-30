<?php

namespace Database\Factories;

use App\Enums\PaymentTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseInvoicePaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'payment_type' => PaymentTypeEnum::CASH->value,
            'amount' => fake()->randomFloat(8, 1, 1000000),
            'remarks' => fake()->sentence(),
        ];
    }

    public function setPaymentTypeCash(): self
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PaymentTypeEnum::CASH->value,
        ]);
    }

    public function setPaymentTypeDownPayment(): self
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PaymentTypeEnum::DOWN_PAYMENT->value,
        ]);
    }

    public function setPaymentTypeReturn(): self
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => PaymentTypeEnum::RETURN->value,
        ]);
    }
}
