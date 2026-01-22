<?php

namespace App\Rules;

use App\Actions\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReceiptProductUnitSerialUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseReceiptProductUnitSerial;

    public function __construct($companyId, $purchaseReceiptProductUnitSerial)
    {
        $this->companyId = $companyId;
        $this->purchaseReceiptProductUnitSerial = $purchaseReceiptProductUnitSerial;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReceiptProductUnitSerialActions = new PurchaseReceiptProductUnitSerialActions();

            if (! $purchaseReceiptProductUnitSerialActions->isUniqueCode($this->companyId, $value, $this->purchaseReceiptProductUnitSerial->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
