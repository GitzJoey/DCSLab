<?php

namespace App\DTOs;

final class AssetAdjustmentOutItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $assetAdjustmentId,
        public readonly int $assetAdjustmentOutItemId,
        public readonly string $serial,
    ) {
    }
}
