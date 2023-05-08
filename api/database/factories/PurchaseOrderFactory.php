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
        $faker = \Faker\Factory::create('id_ID');
        $rand = new RandomizerActions();

        return [
            'invoice_code' => $rand->generateAlpha().$rand->generateNumeric(),
            'invoice_date' => $faker->date(),
            'shipping_date' => $faker->date(),
            'shipping_address' => $faker->address(),
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
}
