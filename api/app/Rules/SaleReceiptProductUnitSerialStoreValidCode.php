<?php

namespace App\Rules;

use App\Actions\SaleReceiptProductUnitSerial\SaleReceiptProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleReceiptProductUnitSerialStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleReceiptProductUnitSerialActions = new SaleReceiptProductUnitSerialActions();

            if (! $saleReceiptProductUnitSerialActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
