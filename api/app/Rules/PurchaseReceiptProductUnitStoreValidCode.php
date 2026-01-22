<?php

namespace App\Rules;

use App\Actions\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReceiptProductUnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReceiptProductUnitActions = new PurchaseReceiptProductUnitActions();

            if (! $purchaseReceiptProductUnitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
