<?php

namespace App\DTOs;

final class ProductUnitCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $productId,
        public readonly string $code,
        public readonly bool $isManufacturerSku,
        public readonly int $unitId,
        public readonly bool $isBase,
        public readonly float $conversionValue,
        public readonly bool $isPrimaryUnit,
        public readonly int $point,
        public readonly ?string $remarks,
    ) {
    }
}
