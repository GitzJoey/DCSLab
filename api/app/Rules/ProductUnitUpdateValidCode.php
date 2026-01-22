<?php

namespace App\Rules;

use App\Actions\ProductUnit\ProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $productUnit;

    public function __construct($companyId, $productUnit)
    {
        $this->companyId = $companyId;
        $this->productUnit = $productUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $productUnitActions = new ProductUnitActions();

            if (! $productUnitActions->isUniqueCode($this->companyId, $value, $this->productUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
