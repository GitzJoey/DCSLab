<?php

namespace App\DTOs;

final class AssetCategoryCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
        public readonly int $estimatedUsefulLifeMonths,
        public readonly string $remarks,
    ) {
    }
}
