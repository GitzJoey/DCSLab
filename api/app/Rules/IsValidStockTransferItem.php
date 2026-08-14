<?php

namespace App\Rules;

use App\Models\StockTransferItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidStockTransferItem implements ValidationRule
{
    public function __construct(
        private ?int $companyId = null,
        private ?int $stockTransferId = null
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || is_null($this->companyId) || is_null($this->stockTransferId)) {
            return;
        }

        $isValid = StockTransferItem::query()
            ->where('id', $value)
            ->where('company_id', $this->companyId)
            ->where('stock_transfer_id', $this->stockTransferId)
            ->exists();

        if (! $isValid) {
            $fail('rules.valid_stock_transfer_item')->translate();
        }
    }
}
