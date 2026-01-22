<?php

namespace App\Rules;

use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderProductUnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderProductUnitActions = new PurchaseOrderProductUnitActions();

            if (! $purchaseOrderProductUnitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
