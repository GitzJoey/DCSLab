<?php

namespace App\DTOs;

final class IncomePaymentCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $incomeId,
        public readonly int $cashAccountId,
        public readonly float|int $amount,
        public readonly ?string $remarks,
    ) {
    }
}
