<?php

namespace App\Rules;

use App\Actions\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseAdditionalCostCategoryStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseAdditionalCostCategoryActions = new PurchaseAdditionalCostCategoryActions();

            if (! $purchaseAdditionalCostCategoryActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
