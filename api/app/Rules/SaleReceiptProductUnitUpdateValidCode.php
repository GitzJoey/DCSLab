<?php

namespace App\Rules;

use App\Actions\SaleReceiptProductUnit\SaleReceiptProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleReceiptProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleReceiptProductUnit;

    public function __construct($companyId, $saleReceiptProductUnit)
    {
        $this->companyId = $companyId;
        $this->saleReceiptProductUnit = $saleReceiptProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleReceiptProductUnitActions = new SaleReceiptProductUnitActions();

            if (! $saleReceiptProductUnitActions->isUniqueCode($this->companyId, $value, $this->saleReceiptProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
