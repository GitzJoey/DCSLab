<?php

namespace App\Rules;

use App\Actions\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReturnAdditionalCostCategoryUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseReturnAdditionalCostCategory;

    public function __construct($companyId, $purchaseReturnAdditionalCostCategory)
    {
        $this->companyId = $companyId;
        $this->purchaseReturnAdditionalCostCategory = $purchaseReturnAdditionalCostCategory;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReturnAdditionalCostCategoryActions = new PurchaseReturnAdditionalCostCategoryActions();

            if (! $purchaseReturnAdditionalCostCategoryActions->isUniqueCode($this->companyId, $value, $this->purchaseReturnAdditionalCostCategory->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
