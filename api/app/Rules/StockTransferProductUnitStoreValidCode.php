<?php

namespace App\Rules;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferProductUnitStoreValidCode implements ValidationRule
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferProductUnitActions = new StockTransferProductUnitActions();

            if (! $stockTransferProductUnitActions->isUniqueCode($this->companyId, $value, null)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
