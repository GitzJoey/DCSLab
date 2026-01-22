<?php

namespace App\Rules;

use App\Actions\PurchaseProductUnitSerial\PurchaseProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseProductUnitSerialUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseProductUnitSerial;

    public function __construct($companyId, $purchaseProductUnitSerial)
    {
        $this->companyId = $companyId;
        $this->purchaseProductUnitSerial = $purchaseProductUnitSerial;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseProductUnitSerialActions = new PurchaseProductUnitSerialActions();

            if (! $purchaseProductUnitSerialActions->isUniqueCode($this->companyId, $value, $this->purchaseProductUnitSerial->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
