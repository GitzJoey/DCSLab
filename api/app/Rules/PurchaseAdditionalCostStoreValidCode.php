<?php

namespace App\Rules;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseAdditionalCostStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseAdditionalCostActions = new PurchaseAdditionalCostActions();

            if (! $purchaseAdditionalCostActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
