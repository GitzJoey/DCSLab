<?php

namespace App\DTOs;

use App\Models\StockAdjustmentOutProduct;

final class StockAdjustmentOutProductSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentOutProductId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentOutProduct(StockAdjustmentOutProduct $stockAdjustmentOutProduct, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentOutProduct->company_id,
            branchId: $stockAdjustmentOutProduct->branch_id,
            stockAdjustmentId: $stockAdjustmentOutProduct->stock_adjustment_id,
            stockAdjustmentOutProductId: $stockAdjustmentOutProduct->id,
            serial: $serial,
        );
    }
}
