<?php

namespace App\Rules;

use App\Models\Investor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InvestorStoreValidName implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $investor = Investor::whereCompanyId($this->companyId)->where('name', $value);

        if ($investor->exists()) {
            $fail('rules.unique_name')->translate();

            return;
        }
    }
}
