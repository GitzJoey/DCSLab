<?php

namespace App\DTOs;

final class PurchaseReceiptItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseReceiptId,
        public readonly bool $hasPurchaseItemProduct,

        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,

        public readonly array $serials,
    ) {
    }
}
