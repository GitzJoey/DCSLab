<?php

namespace App\Rules;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReceiptUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseReceipt;

    public function __construct($companyId, $purchaseReceipt)
    {
        $this->companyId = $companyId;
        $this->purchaseReceipt = $purchaseReceipt;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReceiptActions = new PurchaseReceiptActions();

            if (! $purchaseReceiptActions->isUniqueCode($this->companyId, $value, $this->purchaseReceipt->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
