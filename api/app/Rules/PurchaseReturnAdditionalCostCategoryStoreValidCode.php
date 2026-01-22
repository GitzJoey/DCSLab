<?php

namespace App\Rules;

use App\Actions\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReturnAdditionalCostCategoryStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReturnAdditionalCostCategoryActions = new PurchaseReturnAdditionalCostCategoryActions();

            if (! $purchaseReturnAdditionalCostCategoryActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
