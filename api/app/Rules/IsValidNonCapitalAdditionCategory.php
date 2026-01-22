<?php

namespace App\Rules;

use App\Models\NonCapitalAdditionCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidNonCapitalAdditionCategory implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $category = NonCapitalAdditionCategory::where('id', $value)
            ->where('company_id', $this->companyId)
            ->first();

        if (! $category) {
            $fail('rules.valid_non_capital_addition_category')->translate();
        }
    }
}
