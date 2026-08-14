<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

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
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->tokens->count() > $this->maxTokensPerUser) {
            $fail('rules.too_many_tokens')->translate();
        }
    }
}
