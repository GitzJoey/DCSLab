<?php

namespace App\Rules;

use App\Actions\SaleProductUnitSerial\SaleProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleProductUnitSerialUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $saleProductUnitSerial;

    public function __construct($companyId, $saleProductUnitSerial)
    {
        $this->companyId = $companyId;
        $this->saleProductUnitSerial = $saleProductUnitSerial;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $saleProductUnitSerialActions = new SaleProductUnitSerialActions();

            if (! $saleProductUnitSerialActions->isUniqueCode($this->companyId, $value, $this->saleProductUnitSerial->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
