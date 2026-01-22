<?php

namespace App\Rules;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderActions = new PurchaseOrderActions();

            if (! $purchaseOrderActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
