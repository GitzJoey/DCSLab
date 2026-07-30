<?php

namespace App\DTOs;

final class PurchaseOrderReceiptItemUpdateDTO
{
    public function __construct(
        public readonly ?int $purchaseOrderItemId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,
        /** @var array<int, int> */
        public readonly array $deleteSerialIds,
        /** @var array<int, array<string, mixed>> each serial: id, serial */
        public readonly array $serials,
    ) {
    }
}
