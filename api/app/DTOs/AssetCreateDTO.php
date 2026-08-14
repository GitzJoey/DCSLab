<?php

namespace App\DTOs;

final class AssetCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $assetCategoryId,
        public readonly string $code,
        public readonly string $name,
        public readonly int $assetUnitId,
        public readonly int $status,
        public readonly string $remarks,
    ) {
    }
}
