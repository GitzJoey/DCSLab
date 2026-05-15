<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyStoreValidName implements ValidationRule
{
    public function __construct(
        protected User $user,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->companies()->where('name', (string) $value)->exists()) {
            $fail('rules.unique_name')->translate();
        }
    }
}
