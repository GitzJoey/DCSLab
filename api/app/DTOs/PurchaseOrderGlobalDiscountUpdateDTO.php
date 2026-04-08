<?php

namespace App\DTOs;

final class PurchaseOrderGlobalDiscountUpdateDTO
{
    public function __construct(
        public readonly int $sequence,
        public readonly string $discountType,
        public readonly float $discountValue,
    ) {
    }
}
