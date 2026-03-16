<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInProduct;

final class StockAdjustmentInProductSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly int $stockAdjustmentInProductId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentInProduct(StockAdjustmentInProduct $stockAdjustmentInProduct, string $serial): self
    {
        return new self(
            companyId: $stockAdjustmentInProduct->company_id,
            branchId: $stockAdjustmentInProduct->branch_id,
            stockAdjustmentId: $stockAdjustmentInProduct->stock_adjustment_id,
            stockAdjustmentInProductId: $stockAdjustmentInProduct->id,
            serial: $serial,
        );
    }
}
