<?php

namespace App\DTOs;

final class PurchaseReceiptItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseReceiptId,
        public readonly int $purchaseItemId,

        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitQtyBase,
        public readonly ?string $remarks,

        public readonly array $serials,
    ) {
    }
}
