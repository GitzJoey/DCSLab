<?php

namespace App\DTOs;

final class PurchaseOrderReceiptItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseOrderReceiptId,
        public readonly ?int $purchaseOrderItemId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,
        /** @var array<int, array<string, mixed>> each serial: serial */
        public readonly array $serials,
    ) {
    }
}
