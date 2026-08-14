<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInItem;

final class StockAdjustmentInItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentInItemId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentInItem(StockAdjustmentInItem $stockAdjustmentInItem, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentInItem->company_id,
            branchId: $stockAdjustmentInItem->branch_id,
            stockAdjustmentId: $stockAdjustmentInItem->stock_adjustment_id,
            stockAdjustmentInItemId: $stockAdjustmentInItem->id,
            serial: $serial,
        );
    }
}
