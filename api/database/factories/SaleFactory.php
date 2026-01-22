<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'due_days' => fake()->randomNumber(0, 30),
            'delivery_note_reference' => fake()->sentence(),

            'tax_invoice_number' => fake()->sentence(),
            'tax_invoice_vat_base' => fake()->randomNumber(0, 1000000),
            'tax_invoice_vat' => fake()->randomNumber(0, 1000000),
            'return_tax_invoice_number' => fake()->sentence(),
            'return_tax_invoice_vat_base' => fake()->randomNumber(0, 1000000),
            'return_tax_invoice_vat' => fake()->randomNumber(0, 1000000),

            'remarks' => fake()->sentence(),
            'is_posted' => fake()->boolean(),

            'total' => fake()->randomNumber(0, 1000000),
            'global_discount_rate' => fake()->randomNumber(0, 1000000),
            'global_discount_fixed' => fake()->randomNumber(0, 1000000),
            'additional_cost' => fake()->randomNumber(0, 1000000),
            'rounding' => fake()->randomNumber(0, 1000000),
            'grand_total' => fake()->randomNumber(0, 1000000),

            'return_total' => fake()->randomNumber(0, 1000000),
            'return_global_discount_rate' => fake()->randomNumber(0, 1000000),
            'return_global_discount_fixed' => fake()->randomNumber(0, 1000000),
            'return_rounding' => fake()->randomNumber(0, 1000000),
            'return_grand_total' => fake()->randomNumber(0, 1000000),

            'amount_due' => fake()->randomNumber(0, 1000000),
            'amount_paid_by_sale_order_down_payment' => fake()->randomNumber(0, 1000000),
            'amount_paid_by_sale_return' => fake()->randomNumber(0, 1000000),
            'amount_paid_before_invoice' => fake()->randomNumber(0, 1000000),
            'amount_paid_on_invoice' => fake()->randomNumber(0, 1000000),
            'amount_paid_after_invoice' => fake()->randomNumber(0, 1000000),
            'amount_paid_total' => fake()->randomNumber(0, 1000000),
            'amount_due' => fake()->randomNumber(0, 1000000),

            'is_paid_off' => fake()->boolean(),
            'is_valid' => fake()->boolean(),
        ];
    }
}
