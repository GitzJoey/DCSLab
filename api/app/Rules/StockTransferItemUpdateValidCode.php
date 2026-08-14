<?php

namespace App\Rules;

use App\Actions\StockTransferItem\StockTransferItemActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferItemUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $stockTransferItem;

    public function __construct($companyId, $stockTransferItem)
    {
        $this->companyId = $companyId;
        $this->stockTransferItem = $stockTransferItem;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferItemActions = new StockTransferItemActions();

            if (! $stockTransferItemActions->isUniqueCode($this->companyId, $value, $this->stockTransferItem->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
