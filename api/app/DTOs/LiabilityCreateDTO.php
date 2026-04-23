<?php

namespace App\DTOs;

final class LiabilityCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $categoryId,
        public readonly ?int $creditorId,
        public readonly ?int $supplierId,
        public readonly ?int $cashAccountId,
        public readonly float $amountReceived,
        public readonly float $amountPayable,
        public readonly int $dueDays,
        public readonly ?string $remarks,

        public readonly array $payments,
    ) {
    }
}
