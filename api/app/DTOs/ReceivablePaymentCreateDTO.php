<?php

namespace App\DTOs;

final class ReceivablePaymentCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $receivableId,
        public readonly int $cashAccountId,
        public readonly float|int $amount,
        public readonly ?string $remarks,
    ) {
    }
}
