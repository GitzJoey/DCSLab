<?php

namespace Database\Factories;

use App\Actions\Randomizer\RandomizerActions;
use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $locale = 'id_ID';

        return [
            'invoice_code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'invoice_date' => fake()->date(),
            'shipping_date' => fake()->date(),
            'shipping_address' => fake($locale)->address(),
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
}
