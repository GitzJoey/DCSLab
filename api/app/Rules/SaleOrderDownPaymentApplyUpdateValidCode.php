<?php

namespace App\Rules;

use App\Actions\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleOrderDownPaymentApplyUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleOrderDownPaymentApply;

    public function __construct($companyId, $saleOrderDownPaymentApply)
    {
        $this->companyId = $companyId;
        $this->saleOrderDownPaymentApply = $saleOrderDownPaymentApply;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleOrderDownPaymentApplyActions = new SaleOrderDownPaymentApplyActions();

            if (! $saleOrderDownPaymentApplyActions->isUniqueCode($this->companyId, $value, $this->saleOrderDownPaymentApply->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
