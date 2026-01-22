<?php

namespace App\Rules;

use App\Actions\SaleOrderDownPayment\SaleOrderDownPaymentActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleOrderDownPaymentUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleOrderDownPayment;

    public function __construct($companyId, $saleOrderDownPayment)
    {
        $this->companyId = $companyId;
        $this->saleOrderDownPayment = $saleOrderDownPayment;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleOrderDownPaymentActions = new SaleOrderDownPaymentActions();

            if (! $saleOrderDownPaymentActions->isUniqueCode($this->companyId, $value, $this->saleOrderDownPayment->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
