<?php

namespace App\Rules;

use App\Actions\ProductUnit\ProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductUnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $productUnitActions = new ProductUnitActions();

            if (! $productUnitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
