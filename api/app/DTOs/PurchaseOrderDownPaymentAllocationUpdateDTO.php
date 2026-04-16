<?php

namespace App\DTOs;

final class PurchaseOrderDownPaymentAllocationUpdateDTO
{
    public function __construct(
        public readonly int $purchaseOrderDownPaymentId,
        public readonly int $purchaseId,
        public readonly string $date,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
