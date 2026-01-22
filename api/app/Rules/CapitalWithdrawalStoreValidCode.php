<?php

namespace App\Rules;

use App\Actions\CapitalWithdrawal\CapitalWithdrawalActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CapitalWithdrawalStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $capitalWithdrawalActions = new CapitalWithdrawalActions();

            if (! $capitalWithdrawalActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
