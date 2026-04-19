<?php

namespace App\DTOs;

final class PurchaseReceiptCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $warehouseId,
        public readonly ?string $remarks,
        public readonly bool $isPosted,

        public readonly array $items,
    ) {
    }
}
