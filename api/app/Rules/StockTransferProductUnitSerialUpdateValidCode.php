<?php

namespace App\Rules;

use App\Actions\StockTransferProductUnitSerial\StockTransferProductUnitSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferProductUnitSerialUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $stockTransferProductUnitSerial;

    public function __construct($companyId, $stockTransferProductUnitSerial)
    {
        $this->companyId = $companyId;
        $this->stockTransferProductUnitSerial = $stockTransferProductUnitSerial;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferProductUnitSerialActions = new StockTransferProductUnitSerialActions();

            if (! $stockTransferProductUnitSerialActions->isUniqueCode($this->companyId, $value, $this->stockTransferProductUnitSerial->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
