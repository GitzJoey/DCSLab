<?php

namespace App\DTOs;

final class StockAdjustmentInProductCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitCogs,
        public readonly ?string $remarks,
    ) {
    }
}
