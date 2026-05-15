<?php

namespace App\Rules;

use App\Models\Branch;
use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BranchUpdateValidName implements ValidationRule
{
    public function __construct(
        protected ?int $companyId,
        protected Branch $branch,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = Company::find($this->companyId);
        if (! $company) {
            return;
        }

        if ($company->branches()
            ->where('branches.id', '<>', $this->branch->id)
            ->where('name', (string) $value)
            ->exists()) {
            $fail('rules.unique_name')->translate();
        }
    }
}
