<?php

namespace App\DTOs;

final class CashTransferUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $sourceCashAccountId,
        public readonly int $destinationCashAccountId,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
