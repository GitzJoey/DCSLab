<?php

namespace App\DTOs;

final class PurchaseReturnRefundCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseReturnId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $cashAccountId,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
