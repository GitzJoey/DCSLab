<?php

namespace App\Rules;

use App\Actions\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReturnProductUnitSerialStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReturnProductUnitSerialActions = new PurchaseReturnProductUnitSerialActions();

            if (! $purchaseReturnProductUnitSerialActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
