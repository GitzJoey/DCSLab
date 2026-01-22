<?php

namespace App\Rules;

use App\Actions\Investor\InvestorActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InvestorUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $investor;

    public function __construct($companyId, $investor)
    {
        $this->companyId = $companyId;
        $this->investor = $investor;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $investorActions = new InvestorActions();

            if (! $investorActions->isUniqueCode($this->companyId, $value, $this->investor->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
