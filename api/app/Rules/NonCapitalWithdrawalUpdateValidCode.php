<?php

namespace App\Rules;

use App\Actions\NonCapitalWithdrawal\NonCapitalWithdrawalActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NonCapitalWithdrawalUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $nonCapitalWithdrawal;

    public function __construct($companyId, $nonCapitalWithdrawal)
    {
        $this->companyId = $companyId;
        $this->nonCapitalWithdrawal = $nonCapitalWithdrawal;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $nonCapitalWithdrawalActions = new NonCapitalWithdrawalActions();

            if (! $nonCapitalWithdrawalActions->isUniqueCode($this->companyId, $value, $this->nonCapitalWithdrawal->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
