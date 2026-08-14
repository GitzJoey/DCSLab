<?php

namespace App\DTOs;

final class ProductUnitUpdateServiceDTO
{
    public function __construct(
        public readonly ?string $remarks,
        public readonly int $unitId,
        public readonly float $price,
        public readonly int $point,
    ) {
    }
}
