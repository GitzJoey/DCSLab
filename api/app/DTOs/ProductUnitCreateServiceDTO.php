<?php

namespace App\DTOs;

final class ProductUnitCreateServiceDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $productId,
        public readonly ?string $remarks,
        public readonly int $unitId,
        public readonly float $price,
        public readonly int $point,
    ) {
    }
}
