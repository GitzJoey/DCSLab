<?php

namespace App\DTOs;

final class PurchaseAdditionalCostPaymentCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $purchaseAdditionalCostId,
        public readonly int $cashAccountId,
        public readonly float|int $amount,
        public readonly ?string $remarks,
    ) {
    }
}
