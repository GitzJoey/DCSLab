<?php

namespace App\Rules;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchasePaymentStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchasePaymentActions = new PurchasePaymentActions();

            if (! $purchasePaymentActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
