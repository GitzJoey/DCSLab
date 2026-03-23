<?php

namespace App\DTOs;

final class StockTransferProductUnitCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockTransferId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,
        public readonly array $serials,
    ) {
    }
}
