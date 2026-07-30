<?php

namespace App\DTOs;

final class SalesReturnRefundCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $salesReturnId,
        public readonly int $cashAccountId,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
