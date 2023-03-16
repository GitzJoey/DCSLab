<?php

namespace App\Rules;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;

class MustResetPassword implements ValidationRule
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
        if (is_null($this->user->password_changed_at)) {
            $fail('rules.must_reset_password')->translate();
        }

        if (Carbon::now()->diffInDays(Carbon::parse($this->user->password_changed_at)->addDays(Config::get('dcslab.PASSWORD_EXPIRY_DAYS')), false) <= 0) {
            $fail('rules.must_reset_password')->translate();
        }
    }
}
