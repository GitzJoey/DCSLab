<?php

namespace App\Rules;

use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseOrderProductUnit;

    public function __construct($companyId, $purchaseOrderProductUnit)
    {
        $this->companyId = $companyId;
        $this->purchaseOrderProductUnit = $purchaseOrderProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderProductUnitActions = new PurchaseOrderProductUnitActions();

            if (! $purchaseOrderProductUnitActions->isUniqueCode($this->companyId, $value, $this->purchaseOrderProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
