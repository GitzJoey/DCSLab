<?php

namespace App\Rules;

use App\Actions\SaleProductUnit\SaleProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleProductUnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleProductUnitActions = new SaleProductUnitActions();

            if (! $saleProductUnitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
