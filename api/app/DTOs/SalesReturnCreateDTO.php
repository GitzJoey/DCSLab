<?php

namespace App\DTOs;

final class SalesReturnCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $customerId,
        public readonly ?int $salesInvoiceId,
        public readonly int $warehouseId,
        public readonly float $globalDiscount,
        public readonly float $rounding,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        public readonly array $items,
        public readonly array $refunds,
    ) {
    }
}
