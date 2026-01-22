<?php

namespace App\Rules;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderDownPaymentUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseOrderDownPayment;

    public function __construct($companyId, $purchaseOrderDownPayment)
    {
        $this->companyId = $companyId;
        $this->purchaseOrderDownPayment = $purchaseOrderDownPayment;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderDownPaymentActions = new PurchaseOrderDownPaymentActions();

            if (! $purchaseOrderDownPaymentActions->isUniqueCode($this->companyId, $value, $this->purchaseOrderDownPayment->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
