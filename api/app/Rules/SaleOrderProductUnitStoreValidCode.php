<?php

namespace App\Rules;

use App\Actions\SaleOrderProductUnit\SaleOrderProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleOrderProductUnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleOrderProductUnitActions = new SaleOrderProductUnitActions();

            if (! $saleOrderProductUnitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
