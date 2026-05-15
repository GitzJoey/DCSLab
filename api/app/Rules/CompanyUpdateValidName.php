<?php

namespace App\Rules;

use App\Models\Company;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyUpdateValidName implements ValidationRule
{
    public function __construct(
        protected User $user,
        protected Company $company,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->companies()
            ->where('companies.id', '<>', $this->company->id)
            ->where('name', (string) $value)
            ->exists()) {
            $fail('rules.unique_name')->translate();
        }
    }
}
