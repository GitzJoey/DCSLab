<?php

namespace App\Rules;

use App\Actions\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReturnProductUnitSerialUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseReturnProductUnitSerial;

    public function __construct($companyId, $purchaseReturnProductUnitSerial)
    {
        $this->companyId = $companyId;
        $this->purchaseReturnProductUnitSerial = $purchaseReturnProductUnitSerial;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReturnProductUnitSerialActions = new PurchaseReturnProductUnitSerialActions();

            if (! $purchaseReturnProductUnitSerialActions->isUniqueCode($this->companyId, $value, $this->purchaseReturnProductUnitSerial->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
