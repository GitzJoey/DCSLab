<?php

namespace App\Rules;

use App\Actions\StockTransferItemSerial\StockTransferItemSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferItemSerialUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $stockTransferItemSerial;

    public function __construct($companyId, $stockTransferItemSerial)
    {
        $this->companyId = $companyId;
        $this->stockTransferItemSerial = $stockTransferItemSerial;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferItemSerialActions = new StockTransferItemSerialActions();

            if (! $stockTransferItemSerialActions->isUniqueCode($this->companyId, $value, $this->stockTransferItemSerial->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
