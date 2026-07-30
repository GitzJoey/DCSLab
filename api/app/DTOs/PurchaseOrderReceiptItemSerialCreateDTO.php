<?php

namespace App\DTOs;

final class PurchaseOrderReceiptItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseOrderReceiptId,
        public readonly int $purchaseOrderReceiptItemId,
        public readonly string $serial,
    ) {
    }
}
