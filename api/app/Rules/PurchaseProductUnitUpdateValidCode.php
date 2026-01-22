<?php

namespace App\Rules;

use App\Actions\PurchaseProductUnit\PurchaseProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseProductUnit;

    public function __construct($companyId, $purchaseProductUnit)
    {
        $this->companyId = $companyId;
        $this->purchaseProductUnit = $purchaseProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseProductUnitActions = new PurchaseProductUnitActions();

            if (! $purchaseProductUnitActions->isUniqueCode($this->companyId, $value, $this->purchaseProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
