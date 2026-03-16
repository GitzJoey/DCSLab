<?php

namespace App\DTOs;

use App\Models\StockAdjustmentOutProductSerial;

final class StockAdjustmentOutProductSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentOutProductId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentOutProductSerial(StockAdjustmentOutProductSerial $stockAdjustmentOutProductSerial, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentOutProductSerial->company_id,
            branchId: $stockAdjustmentOutProductSerial->branch_id,
            stockAdjustmentId: $stockAdjustmentOutProductSerial->stock_adjustment_id,
            stockAdjustmentOutProductId: $stockAdjustmentOutProductSerial->stock_adjustment_out_product_id,
            serial: $serial,
        );
    }
}
