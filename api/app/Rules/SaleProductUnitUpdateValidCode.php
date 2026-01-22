<?php

namespace App\Rules;

use App\Actions\SaleProductUnit\SaleProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleProductUnit;

    public function __construct($companyId, $saleProductUnit)
    {
        $this->companyId = $companyId;
        $this->saleProductUnit = $saleProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleProductUnitActions = new SaleProductUnitActions();

            if (! $saleProductUnitActions->isUniqueCode($this->companyId, $value, $this->saleProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
