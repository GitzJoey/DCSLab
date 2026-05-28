<?php

namespace App\Rules\Company;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use App\Models\User;

class SetCompanyToNonDefault implements ValidationRule
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! boolval($value) &&
            $this->user->companies &&
            $this->user->companies->count() == 1) {
            $fail('rules.company.set_company_to_non_default')->translate();
        }
    }
}
