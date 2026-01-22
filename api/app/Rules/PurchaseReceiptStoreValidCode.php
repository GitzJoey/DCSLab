<?php

namespace App\Rules;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReceiptStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReceiptActions = new PurchaseReceiptActions();

            if (! $purchaseReceiptActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
