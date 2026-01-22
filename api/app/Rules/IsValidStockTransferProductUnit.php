<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidStockTransferProductUnit implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! auth()->stockTransfer()->product_units->pluck('id')->contains($value)) {
            $fail('rules.valid_stock_transfer_product_unit')->translate();
        }
    }
}
