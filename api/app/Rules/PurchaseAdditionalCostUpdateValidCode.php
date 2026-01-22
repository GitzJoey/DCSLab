<?php

namespace App\Rules;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseAdditionalCostUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseAdditionalCost;

    public function __construct($companyId, $purchaseAdditionalCost)
    {
        $this->companyId = $companyId;
        $this->purchaseAdditionalCost = $purchaseAdditionalCost;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseAdditionalCostActions = new PurchaseAdditionalCostActions();

            if (! $purchaseAdditionalCostActions->isUniqueCode($this->companyId, $value, $this->purchaseAdditionalCost->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
