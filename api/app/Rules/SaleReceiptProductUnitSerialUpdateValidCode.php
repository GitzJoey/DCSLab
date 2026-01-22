<?php

namespace App\Rules;

use App\Actions\SaleReceiptProductUnitSerial\SaleReceiptProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleReceiptProductUnitSerialUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleReceiptProductUnitSerial;

    public function __construct($companyId, $saleReceiptProductUnitSerial)
    {
        $this->companyId = $companyId;
        $this->saleReceiptProductUnitSerial = $saleReceiptProductUnitSerial;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleReceiptProductUnitSerialActions = new SaleReceiptProductUnitSerialActions();

            if (! $saleReceiptProductUnitSerialActions->isUniqueCode($this->companyId, $value, $this->saleReceiptProductUnitSerial->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
