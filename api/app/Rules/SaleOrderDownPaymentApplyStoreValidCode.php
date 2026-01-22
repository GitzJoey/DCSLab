<?php

namespace App\Rules;

use App\Actions\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleOrderDownPaymentApplyStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleOrderDownPaymentApplyActions = new SaleOrderDownPaymentApplyActions();

            if (! $saleOrderDownPaymentApplyActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
