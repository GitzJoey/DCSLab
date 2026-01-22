<?php

namespace App\Rules;

use App\Actions\SaleOrderDownPayment\SaleOrderDownPaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleOrderDownPaymentStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleOrderDownPaymentActions = new SaleOrderDownPaymentActions();

            if (! $saleOrderDownPaymentActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
