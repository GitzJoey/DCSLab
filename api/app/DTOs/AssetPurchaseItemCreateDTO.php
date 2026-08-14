<?php

namespace App\DTOs;

final class AssetPurchaseItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $assetPurchaseId,
        public readonly int $assetId,
        public readonly string $qty,
        public readonly string $unitPrice,
        public readonly string $remarks,
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
