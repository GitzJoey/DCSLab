<?php

namespace App\DTOs;

final class PurchaseOrderCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly ?int $supplierId,
        public readonly ?string $remarks,
        public readonly float $rounding,
        public readonly array $globalDiscounts,

        public readonly array $items,
        public readonly array $downPayments,
        public readonly array $refundedDownPayments,
    ) {
    }
}
