<?php

namespace App\Rules;

use App\Actions\SaleReceipt\SaleReceiptActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleReceiptStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleReceiptActions = new SaleReceiptActions();

            if (! $saleReceiptActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
