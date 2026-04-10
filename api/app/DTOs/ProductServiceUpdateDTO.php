<?php

namespace App\DTOs;

final class ProductServiceUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly int $categoryId,
        public readonly ?int $defaultVatProfileId,
        public readonly string $name,
        public readonly bool $isPriceIncludeVat,
        public readonly ?string $remarks,
        public readonly int $status,
        public readonly int $unitId,
        public readonly float $price,
        public readonly int $point,

        public readonly array $deleteImageIds,
        public readonly array $images,
    ) {
    }
}
