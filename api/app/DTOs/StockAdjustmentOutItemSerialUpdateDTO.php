<?php

namespace App\DTOs;

use App\Models\StockAdjustmentOutItemSerial;

final class StockAdjustmentOutItemSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentOutItemId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentOutItemSerial(StockAdjustmentOutItemSerial $stockAdjustmentOutItemSerial, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentOutItemSerial->company_id,
            branchId: $stockAdjustmentOutItemSerial->branch_id,
            stockAdjustmentId: $stockAdjustmentOutItemSerial->stock_adjustment_id,
            stockAdjustmentOutItemId: $stockAdjustmentOutItemSerial->stock_adjustment_out_item_id,
            serial: $serial,
        );
    }
}
