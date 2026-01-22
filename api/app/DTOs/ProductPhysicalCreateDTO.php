<?php

namespace App\DTOs;

final class ProductPhysicalCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly int $categoryId,
        public readonly ?int $brandId,
        public readonly string $name,
        public readonly string $slug,
        public readonly bool $isTaxable,
        public readonly float $vatRate,
        public readonly bool $isPriceIncludeVat,
        public readonly bool $isUseSerialNumber,
        public readonly bool $isExpirable,
        public readonly ?string $remarks,
        public readonly int $type,
        public readonly int $status,
        public readonly array $productUnits,
    ) {
    }
}
