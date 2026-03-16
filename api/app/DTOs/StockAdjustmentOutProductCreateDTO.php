<?php

namespace App\DTOs;

final class StockAdjustmentOutProductCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockAdjustmentId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,
        public readonly array $serials,
    ) {
    }
}
