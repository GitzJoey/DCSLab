<?php

namespace Database\Factories;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => 'SUP-'.fake()->numerify('#####'),
            'name' => fake()->company(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'payment_term_type' => fake()->randomElement(PaymentTermTypeEnum::toArrayEnum()),
            'payment_term' => fake()->numberBetween(1, 30),
            'taxable_enterprise' => fake()->boolean(),
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'status' => fake()->randomElement(RecordStatusEnum::toArrayEnum()),
            'remarks' => fake()->sentence(),
        ];
    }

    public function setStatusActive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatusEnum::ACTIVE->value,
            ];
        });
    }

    public function setStatusInactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatusEnum::INACTIVE->value,
            ];
        });
    }
}
