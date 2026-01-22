<?php

namespace App\Rules;

use App\Actions\Purchase\PurchaseActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PurchaseStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $purchaseActions = new PurchaseActions();

            if (! $purchaseActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
