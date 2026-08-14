<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyUpdateValidDefault implements ValidationRule
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->companies->count() == 1 && $value == false) {
            $fail('rules.company.set_company_to_non_default')->translate();

            return;
        }
    }
}
