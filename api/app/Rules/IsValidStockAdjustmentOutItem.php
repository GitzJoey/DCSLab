<?php

namespace App\Rules;

use App\Models\StockAdjustmentOutItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidStockAdjustmentOutItem implements ValidationRule
{
    public function __construct(
        private ?int $companyId = null,
        private ?int $stockAdjustmentId = null
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || is_null($this->companyId) || is_null($this->stockAdjustmentId)) {
            return;
        }

        $isValid = StockAdjustmentOutItem::query()
            ->where('id', $value)
            ->where('company_id', $this->companyId)
            ->where('stock_adjustment_id', $this->stockAdjustmentId)
            ->exists();

        if (! $isValid) {
            $fail('rules.valid_stock_adjustment_out_item')->translate();
        }
    }
}
