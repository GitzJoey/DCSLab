<?php

namespace App\DTOs;

final class PurchaseReceiptItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseReceiptId,
        public readonly int $purchaseReceiptItemId,
        public readonly string $serial,
    ) {
    }
}
