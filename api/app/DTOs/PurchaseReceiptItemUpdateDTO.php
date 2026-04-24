<?php

namespace App\DTOs;

final class PurchaseReceiptItemUpdateDTO
{
    public function __construct(
        public readonly ?int $purchaseItemId,

        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitQtyBase,
        public readonly ?string $remarks,

        public readonly array $deleteSerialIds,
        public readonly array $serials,
    ) {
    }
}
