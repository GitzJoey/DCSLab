<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseInvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'date' => fake()->date(),
            'due_days' => fake()->numberBetween(0, 60),
            'tax_invoice_number' => fake()->numerify('##########'),
            'tax_invoice_vat_base' => 0,
            'tax_invoice_vat' => 0,
            'remarks' => fake()->sentence(),
            'is_posted' => fake()->boolean(),
            'global_discount' => 0,
            'rounding' => 0,
        ];
    }
}
