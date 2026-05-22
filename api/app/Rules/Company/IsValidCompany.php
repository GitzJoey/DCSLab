<?php

namespace App\Rules\Company;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class IsValidCompany implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! auth()->user()->companies->pluck('id')->contains($value)) {
            $fail('rules.company.valid_company')->translate();
        }
    }
}
