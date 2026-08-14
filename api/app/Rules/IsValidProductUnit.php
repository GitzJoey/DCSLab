<?php

namespace App\Rules;

use App\Models\ProductUnit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidProductUnit implements ValidationRule
{
    public function __construct(
        protected ?int $companyId = null
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || is_null($this->companyId)) {
            return;
        }

        $exists = ProductUnit::query()
            ->where('id', $value)
            ->where('company_id', $this->companyId)
            ->exists();

        if (! $exists) {
            $fail('rules.valid_product_unit')->translate();
        }
    }
}
