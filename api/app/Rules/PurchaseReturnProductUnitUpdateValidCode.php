<?php

namespace App\Rules;

use App\Actions\PurchaseReturnProductUnit\PurchaseReturnProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReturnProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseReturnProductUnit;

    public function __construct($companyId, $purchaseReturnProductUnit)
    {
        $this->companyId = $companyId;
        $this->purchaseReturnProductUnit = $purchaseReturnProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReturnProductUnitActions = new PurchaseReturnProductUnitActions();

            if (! $purchaseReturnProductUnitActions->isUniqueCode($this->companyId, $value, $this->purchaseReturnProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
