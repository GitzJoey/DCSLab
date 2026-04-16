<?php

namespace App\DTOs;

final class PurchaseOrderDownPaymentAllocationCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseOrderDownPaymentId,
        public readonly int $purchaseId,
        public readonly string $date,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
