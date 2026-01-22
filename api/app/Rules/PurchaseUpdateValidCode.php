<?php

namespace App\Rules;

use App\Actions\Purchase\PurchaseActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $purchase;

    public function __construct($companyId, $purchase)
    {
        $this->companyId = $companyId;
        $this->purchase = $purchase;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseActions = new PurchaseActions();

            if (! $purchaseActions->isUniqueCode($this->companyId, $value, $this->purchase->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
