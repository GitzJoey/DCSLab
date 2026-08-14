<?php

namespace App\DTOs;

final class CapitalTransactionCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $investorId,
        public readonly int $cashAccountId,
        public readonly string $type,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
