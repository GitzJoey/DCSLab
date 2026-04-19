<?php

namespace App\DTOs;

final class PurchaseGlobalDiscountUpdateDTO
{
    public function __construct(
        public readonly int $sequence,
        public readonly string $discountType,
        public readonly float $discountValue,
    ) {
    }
}
