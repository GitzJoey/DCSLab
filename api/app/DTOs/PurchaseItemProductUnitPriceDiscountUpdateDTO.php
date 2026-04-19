<?php

namespace App\DTOs;

final class PurchaseItemProductUnitPriceDiscountUpdateDTO
{
    public function __construct(
        public readonly int $sequence,
        public readonly string $discountType,
        public readonly float $discountValue,
    ) {
    }
}
