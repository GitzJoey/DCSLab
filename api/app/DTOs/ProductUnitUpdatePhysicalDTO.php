<?php

namespace App\DTOs;

final class ProductUnitUpdatePhysicalDTO
{
    public function __construct(
        public readonly string $code,
        public readonly bool $isManufacturerSku,
        public readonly int $unitId,
        public readonly float $price,
        public readonly float $conversionValue,
        public readonly bool $isPrimaryUnit,
        public readonly int $point,
        public readonly ?string $remarks,
    ) {
    }
}
