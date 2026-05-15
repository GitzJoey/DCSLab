<?php

namespace App\DTOs;

final class ProductPhysicalCreateDTO
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

        /** @var array<int, array<string, mixed>> */
        public readonly array $productUnits,
        /** @var array<int, array<string, mixed>> */
        public readonly array $images,
    ) {
    }
}
