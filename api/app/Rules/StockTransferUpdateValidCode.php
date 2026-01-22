<?php

namespace App\Rules;

use App\Actions\StockTransfer\StockTransferActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $stockTransfer;

    public function __construct($companyId, $stockTransfer)
    {
        $this->companyId = $companyId;
        $this->stockTransfer = $stockTransfer;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferActions = new StockTransferActions();

            if (! $stockTransferActions->isUniqueCode($this->companyId, $value, $this->stockTransfer->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
