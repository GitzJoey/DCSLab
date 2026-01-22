<?php

namespace App\Rules;

use App\Actions\SalePayment\SalePaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SalePaymentUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $salePayment;

    public function __construct($companyId, $salePayment)
    {
        $this->companyId = $companyId;
        $this->salePayment = $salePayment;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $salePaymentActions = new SalePaymentActions();

            if (! $salePaymentActions->isUniqueCode($this->companyId, $value, $this->salePayment->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
