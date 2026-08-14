<?php

namespace App\DTOs;

final class AssetUpdateDTO
{
    public function __construct(
        public readonly int $assetCategoryId,
        public readonly string $code,
        public readonly string $name,
        public readonly int $assetUnitId,
        public readonly int $status,
        public readonly string $remarks,
    ) {
    }
}
