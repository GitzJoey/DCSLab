<?php

namespace App\Rules;

use App\Models\Company;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyUpdateValidCode implements ValidationRule
{
    public function __construct(
        protected User $user,
        protected Company $company,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === config('dcslab.KEYWORDS.AUTO')) {
            return;
        }

        if ($this->user->companies()
            ->where('companies.id', '<>', $this->company->id)
            ->where('code', (string) $value)
            ->exists()) {
            $fail('rules.unique_code')->translate();
        }
    }
}
