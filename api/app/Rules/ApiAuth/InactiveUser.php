<?php

namespace App\Rules;

use App\Enums\RecordStatus;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class InactiveUser implements ValidationRule
{
    private User $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->profile->status == RecordStatus::INACTIVE) {
            $fail('rules.api_auth.inactive_user')->translate();
        }
    }
}
