<?php

namespace App\Rules;

use App\Actions\StockTransferItem\StockTransferItemActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferItemStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferItemActions = new StockTransferItemActions();

            if (! $stockTransferItemActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
