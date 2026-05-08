<?php

namespace App\DTOs;

final class PurchaseReceiptDirectItemUpdateDTO
{
    public function __construct(
        public readonly int $purchaseItemId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,
        public readonly array $deleteSerialIds,
        public readonly array $serials,
    ) {
    }
}
