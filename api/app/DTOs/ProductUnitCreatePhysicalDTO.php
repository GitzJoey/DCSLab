<?php

namespace App\DTOs;

final class ProductUnitCreatePhysicalDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $productId,
        public readonly string $code,
        public readonly bool $isManufacturerSKU,
        public readonly int $unitId,
        public readonly float $price,
        public readonly float $conversionValue,
        public readonly bool $isPrimaryUnit,
        public readonly int $point,
        public readonly ?string $remarks,
    ) {
    }
}
