<?php

namespace App\DTOs;

final class PurchaseGlobalDiscountCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseId,
        public readonly int $sequence,
        public readonly string $discountType,
        public readonly float $discountValue,
    ) {
    }
}
