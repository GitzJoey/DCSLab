<?php

namespace App\Rules;

use App\Actions\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderDownPaymentApplyStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderDownPaymentApplyActions = new PurchaseOrderDownPaymentApplyActions();

            if (! $purchaseOrderDownPaymentApplyActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
