<?php

namespace App\Rules;

use App\Actions\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseAdditionalCostCategoryUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseAdditionalCostCategory;

    public function __construct($companyId, $purchaseAdditionalCostCategory)
    {
        $this->companyId = $companyId;
        $this->purchaseAdditionalCostCategory = $purchaseAdditionalCostCategory;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseAdditionalCostCategoryActions = new PurchaseAdditionalCostCategoryActions();

            if (! $purchaseAdditionalCostCategoryActions->isUniqueCode($this->companyId, $value, $this->purchaseAdditionalCostCategory->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
