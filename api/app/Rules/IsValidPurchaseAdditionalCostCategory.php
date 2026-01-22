<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidPurchaseAdditionalCostCategory implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! auth()->user()->purchaseAdditionalCostCategories->pluck('id')->contains($value)) {
            $fail('rules.valid_purchase_additional_cost_category')->translate();
        }
    }
}
