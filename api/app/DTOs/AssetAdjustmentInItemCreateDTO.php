<?php

namespace App\DTOs;

final class AssetAdjustmentInItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $assetAdjustmentId,
        public readonly int $assetId,
        public readonly float $qty,
        public readonly string $remarks,
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
