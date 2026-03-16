<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInProductSerial;

final class StockAdjustmentInProductSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentInProductId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentInProductSerial(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentInProductSerial->company_id,
            branchId: $stockAdjustmentInProductSerial->branch_id,
            stockAdjustmentId: $stockAdjustmentInProductSerial->stock_adjustment_id,
            stockAdjustmentInProductId: $stockAdjustmentInProductSerial->stock_adjustment_in_product_id,
            serial: $serial,
        );
    }
}
