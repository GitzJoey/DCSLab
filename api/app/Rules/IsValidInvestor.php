<?php

namespace App\Rules;

use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidInvestor implements ValidationRule
{
    public ?int $companyId;

    public function __construct(?int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->companyId && $value) {
            if (! auth()->user()->companies?->pluck('id')->contains($this->companyId)) {
                // next change message
                $fail('rules.valid_company')->translate();
            }

            $company = Company::find($this->companyId);

            if (! $company->investors?->pluck('id')->contains($value)) {
                $fail('rules.valid_investor')->translate();
            }
        }
    }
}
