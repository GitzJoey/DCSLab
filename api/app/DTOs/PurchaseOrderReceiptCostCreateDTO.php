<?php

namespace App\DTOs;

final class PurchaseOrderReceiptCostCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseOrderReceiptId,
        public readonly string $code,
        public readonly string $date,
        public readonly string $name,
        public readonly int $cashAccountId,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
