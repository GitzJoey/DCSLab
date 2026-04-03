<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInItemSerial;

final class StockAdjustmentInItemSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentInItemId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentInItemSerial(StockAdjustmentInItemSerial $stockAdjustmentInItemSerial, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentInItemSerial->company_id,
            branchId: $stockAdjustmentInItemSerial->branch_id,
            stockAdjustmentId: $stockAdjustmentInItemSerial->stock_adjustment_id,
            stockAdjustmentInItemId: $stockAdjustmentInItemSerial->stock_adjustment_in_item_id,
            serial: $serial,
        );
    }
}
