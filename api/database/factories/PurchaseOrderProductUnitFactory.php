<?php

namespace Database\Factories;

use App\Enums\VatStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrderProductUnit>
 */
class PurchaseOrderProductUnitFactory extends Factory
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
            'qty' => fake($locale)->numberBetween(1, 100),
            'product_unit_amount_per_unit' => 1,
            'product_unit_amount_total' => 0,
            'product_unit_initial_price' => fake($locale)->randomFloat(-4, 1000, 10000000),
            'product_unit_per_unit_discount' => 0,
            'product_unit_sub_total' => 0,
            'product_unit_per_unit_sub_total_discount' => 0,
            'product_unit_total' => 0,
            'product_unit_global_discount_percent' => 0,
            'product_unit_global_discount_nominal' => 0,
            'product_unit_final_price' => 0,
            'vat_status' => fake($locale)->randomElement(VatStatus::toArrayEnum()),
            'vat_rate' => random_int(1, 99) / 100,
            'vat_amount' => fake($locale)->randomFloat(-4, 1000, 10000000),
            'remarks' => fake($locale)->sentence(),
        ];
    }

    public function setAmountPerUnit($val)
    {
        return $this->state(function (array $attributes) use ($val) {
            return [
                'product_unit_amount_per_unit' => $val,
            ];
        });
    }

    public function setInitialPrice($val)
    {
        return $this->state(function (array $attributes) use ($val) {
            return [
                'product_unit_initial_price' => $val,
            ];
        });
    }

    public function setNonVatStatus()
    {
        return $this->state(function (array $attributes) {
            return [
                'vat_status' => VatStatus::NON_VAT,
            ];
        });
    }

    public function setIncludeVatStatus()
    {
        return $this->state(function (array $attributes) {
            return [
                'vat_status' => VatStatus::INCLUDE_VAT,
            ];
        });
    }

    public function setExcludeVatStatus()
    {
        return $this->state(function (array $attributes) {
            return [
                'vat_status' => VatStatus::EXCLUDE_VAT,
            ];
        });
    }
}
