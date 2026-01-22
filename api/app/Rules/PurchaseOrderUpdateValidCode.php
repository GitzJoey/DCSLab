<?php

namespace App\Rules;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseOrderUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchaseOrder;

    public function __construct($companyId, $purchaseOrder)
    {
        $this->companyId = $companyId;
        $this->purchaseOrder = $purchaseOrder;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseOrderActions = new PurchaseOrderActions();

            if (! $purchaseOrderActions->isUniqueCode($this->companyId, $value, $this->purchaseOrder->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
