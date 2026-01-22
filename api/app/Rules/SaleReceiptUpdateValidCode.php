<?php

namespace App\Rules;

use App\Actions\SaleReceipt\SaleReceiptActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleReceiptUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleReceipt;

    public function __construct($companyId, $saleReceipt)
    {
        $this->companyId = $companyId;
        $this->saleReceipt = $saleReceipt;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleReceiptActions = new SaleReceiptActions();

            if (! $saleReceiptActions->isUniqueCode($this->companyId, $value, $this->saleReceipt->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
