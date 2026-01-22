<?php

namespace App\Rules;

use App\Actions\SalePayment\SalePaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SalePaymentStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $salePaymentActions = new SalePaymentActions();

            if (! $salePaymentActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
