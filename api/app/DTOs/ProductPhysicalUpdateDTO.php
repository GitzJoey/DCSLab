<?php

namespace App\DTOs;

final class ProductPhysicalUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly int $categoryId,
        public readonly ?int $brandId,
        public readonly ?int $defaultVatProfileId,
        public readonly string $name,
        public readonly bool $isPriceIncludeVat,
        public readonly bool $isUseSerialNumber,
        public readonly bool $isExpirable,
        public readonly ?string $remarks,
        public readonly int $type,
        public readonly int $status,

        public readonly array $deleteProductUnitIds,
        public readonly array $productUnits,

        public readonly array $deleteImageIds,
        public readonly array $images,
    ) {
    }
}
