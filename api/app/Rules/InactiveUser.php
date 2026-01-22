<?php

namespace App\Rules;

use App\Enums\RecordStatusEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

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
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->profile->status == RecordStatusEnum::INACTIVE) {
            $fail('rules.inactive_user')->translate();
        }
    }
}
