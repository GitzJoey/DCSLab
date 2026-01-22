<?php

namespace App\Rules;

use App\Actions\CapitalWithdrawal\CapitalWithdrawalActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CapitalWithdrawalUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $capitalWithdrawal;

    public function __construct($companyId, $capitalWithdrawal)
    {
        $this->companyId = $companyId;
        $this->capitalWithdrawal = $capitalWithdrawal;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $capitalWithdrawalActions = new CapitalWithdrawalActions();

            if (! $capitalWithdrawalActions->isUniqueCode($this->companyId, $value, $this->capitalWithdrawal->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
