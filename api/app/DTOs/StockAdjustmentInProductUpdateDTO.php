<?php

namespace App\DTOs;

final class StockAdjustmentInProductUpdateDTO
{
    public function __construct(
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitCogs,
        public readonly ?string $remarks,

        public readonly array $deleteSerialIds,
        public readonly array $serials,
    ) {
    }
}
