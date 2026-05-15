<?php

namespace App\Rules;

use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BranchStoreValidCode implements ValidationRule
{
    public function __construct(
        protected ?int $companyId,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === config('dcslab.KEYWORDS.AUTO')) {
            return;
        }

        $company = Company::find($this->companyId);
        if (! $company) {
            return;
        }

        if ($company->branches()->where('code', (string) $value)->exists()) {
            $fail('rules.unique_code')->translate();
        }
    }
}
