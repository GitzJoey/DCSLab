<?php

namespace App\Rules\ApiAuth;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class MaxTokens implements ValidationRule
{
    private int $maxTokensPerUser;

    private User $user;

    public function __construct($user)
    {
        $this->user = $user;
        $this->maxTokensPerUser = 2;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->tokens->count() > $this->maxTokensPerUser) {
            $fail('rules.api_auth.max_tokens')->translate();
        }
    }
}
