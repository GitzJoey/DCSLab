<?php

namespace App\Rules;

use App\Actions\SaleProductUnitSerial\SaleProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleProductUnitSerialStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleProductUnitSerialActions = new SaleProductUnitSerialActions();

            if (! $saleProductUnitSerialActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
