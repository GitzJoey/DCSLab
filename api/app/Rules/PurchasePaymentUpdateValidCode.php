<?php

namespace App\Rules;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchasePaymentUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchasePayment;

    public function __construct($companyId, $purchasePayment)
    {
        $this->companyId = $companyId;
        $this->purchasePayment = $purchasePayment;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchasePaymentActions = new PurchasePaymentActions();

            if (! $purchasePaymentActions->isUniqueCode($this->companyId, $value, $this->purchasePayment->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
