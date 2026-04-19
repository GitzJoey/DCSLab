<?php

namespace App\DTOs;

final class PurchaseItemProductUnitPriceDiscountCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseItemId,
        public readonly int $sequence,
        public readonly string $discountType,
        public readonly float $discountValue,
    ) {
    }
}
