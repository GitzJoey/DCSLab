<?php

namespace App\Rules;

use App\Actions\PurchaseReturnProductUnit\PurchaseReturnProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReturnProductUnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReturnProductUnitActions = new PurchaseReturnProductUnitActions();

            if (! $purchaseReturnProductUnitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
