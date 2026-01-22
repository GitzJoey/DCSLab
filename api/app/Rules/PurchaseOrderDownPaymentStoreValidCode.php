<?php

namespace App\Rules;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderDownPaymentStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderDownPaymentActions = new PurchaseOrderDownPaymentActions();

            if (! $purchaseOrderDownPaymentActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
