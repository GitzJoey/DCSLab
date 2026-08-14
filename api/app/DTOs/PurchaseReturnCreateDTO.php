<?php

namespace App\DTOs;

final class PurchaseReturnCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $supplierId,
        public readonly ?int $purchaseInvoiceId,
        public readonly int $warehouseId,
        public readonly float $globalDiscount,
        public readonly float $rounding,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
        /** @var array<int, array<string, mixed>> */
        public readonly array $refunds,
    ) {
    }
}
