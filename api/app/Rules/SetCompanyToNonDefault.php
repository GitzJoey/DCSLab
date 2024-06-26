<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

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
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
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
