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
        /** @var int[] */
        public readonly array $deleteSerialIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
