<?php

namespace App\Rules;

use App\Actions\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseReturnAdditionalCostUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseReturnAdditionalCost;

    public function __construct($companyId, $purchaseReturnAdditionalCost)
    {
        $this->companyId = $companyId;
        $this->purchaseReturnAdditionalCost = $purchaseReturnAdditionalCost;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseReturnAdditionalCostActions = new PurchaseReturnAdditionalCostActions();

            if (! $purchaseReturnAdditionalCostActions->isUniqueCode($this->companyId, $value, $this->purchaseReturnAdditionalCost->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
