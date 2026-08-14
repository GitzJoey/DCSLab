<?php

namespace App\Rules;

use App\Actions\Investor\InvestorActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InvestorStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $investorActions = new InvestorActions();

            if (! $investorActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
