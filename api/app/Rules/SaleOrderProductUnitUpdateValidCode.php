<?php

namespace App\Rules;

use App\Actions\SaleOrderProductUnit\SaleOrderProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleOrderProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleOrderProductUnit;

    public function __construct($companyId, $saleOrderProductUnit)
    {
        $this->companyId = $companyId;
        $this->saleOrderProductUnit = $saleOrderProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleOrderProductUnitActions = new SaleOrderProductUnitActions();

            if (! $saleOrderProductUnitActions->isUniqueCode($this->companyId, $value, $this->saleOrderProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
