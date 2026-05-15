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
        /** @var array<int, array<string, mixed>> */
        public readonly array $globalDiscounts,

        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
        /** @var array<int, array<string, mixed>> */
        public readonly array $downPayments,
        /** @var array<int, array<string, mixed>> */
        public readonly array $refundedDownPayments,
    ) {
    }
}
