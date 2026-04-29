<?php

namespace App\DTOs;

final class PurchaseReceiptItemUpdateDTO
{
    public function __construct(
        public readonly bool $hasPurchaseItemProduct,

        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,

        public readonly array $deleteSerialIds,
        public readonly array $serials,
    ) {
    }
}
