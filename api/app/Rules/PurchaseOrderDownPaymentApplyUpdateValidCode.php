<?php

namespace App\Rules;

use App\Actions\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderDownPaymentApplyUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseOrderDownPaymentApply;

    public function __construct($companyId, $purchaseOrderDownPaymentApply)
    {
        $this->companyId = $companyId;
        $this->purchaseOrderDownPaymentApply = $purchaseOrderDownPaymentApply;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderDownPaymentApplyActions = new PurchaseOrderDownPaymentApplyActions();

            if (! $purchaseOrderDownPaymentApplyActions->isUniqueCode($this->companyId, $value, $this->purchaseOrderDownPaymentApply->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
