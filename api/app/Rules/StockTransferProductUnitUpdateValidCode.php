<?php

namespace App\Rules;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockTransferProductUnitUpdateValidCode implements ValidationRule
{
    protected $companyId;

    protected $stockTransferProductUnit;

    public function __construct($companyId, $stockTransferProductUnit)
    {
        $this->companyId = $companyId;
        $this->stockTransferProductUnit = $stockTransferProductUnit;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== config('dcslab.KEYWORDS.AUTO')) {
            $stockTransferProductUnitActions = new StockTransferProductUnitActions();

            if (! $stockTransferProductUnitActions->isUniqueCode($this->companyId, $value, $this->stockTransferProductUnit->id)) {
                $fail('rules.unique_code')->translate();

                return;
            }
        }
    }
}
