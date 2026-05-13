<?php

namespace App\DTOs;

final class AssetCategoryUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?int $estimatedUsefulLifeMonths,
        public readonly ?string $remarks,
    ) {
    }
}
