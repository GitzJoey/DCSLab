<?php

namespace App\Rules;

use App\Actions\StockTransferItemSerial\StockTransferItemSerialActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferItemSerialStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferItemSerialActions = new StockTransferItemSerialActions();

            if (! $stockTransferItemSerialActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
