<?php

namespace App\DTOs;

final class SalesOrderCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly ?int $customerId,
        public readonly ?string $remarks,
        public readonly float $globalDiscount,
        public readonly float $rounding,

        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
        /** @var array<int, array<string, mixed>> */
        public readonly array $payments,
        /** @var array<int, array<string, mixed>> */
        public readonly array $refundedPayments,
    ) {
    }
}
