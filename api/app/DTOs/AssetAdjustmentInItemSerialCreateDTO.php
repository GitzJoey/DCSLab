<?php

namespace App\DTOs;

final class AssetAdjustmentInItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $assetAdjustmentId,
        public readonly int $assetAdjustmentInItemId,
        public readonly string $serial,
    ) {
    }
}
