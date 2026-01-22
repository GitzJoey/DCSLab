<?php

namespace App\Rules;

use App\Actions\PurchaseProductUnitSerial\PurchaseProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseProductUnitSerialStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseProductUnitSerialActions = new PurchaseProductUnitSerialActions();

            if (! $purchaseProductUnitSerialActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
