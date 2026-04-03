<?php

namespace App\DTOs;

use App\Models\StockAdjustmentOutItem;

final class StockAdjustmentOutItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentOutItemId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentOutItem(StockAdjustmentOutItem $stockAdjustmentOutItem, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentOutItem->company_id,
            branchId: $stockAdjustmentOutItem->branch_id,
            stockAdjustmentId: $stockAdjustmentOutItem->stock_adjustment_id,
            stockAdjustmentOutItemId: $stockAdjustmentOutItem->id,
            serial: $serial,
        );
    }
}
