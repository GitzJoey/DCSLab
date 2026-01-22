<?php

namespace App\Rules;

use App\Actions\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReceiptProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseReceiptProductUnit;

    public function __construct($companyId, $purchaseReceiptProductUnit)
    {
        $this->companyId = $companyId;
        $this->purchaseReceiptProductUnit = $purchaseReceiptProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReceiptProductUnitActions = new PurchaseReceiptProductUnitActions();

            if (! $purchaseReceiptProductUnitActions->isUniqueCode($this->companyId, $value, $this->purchaseReceiptProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
