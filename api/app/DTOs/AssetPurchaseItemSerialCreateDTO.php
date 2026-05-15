<?php

namespace App\DTOs;

final class AssetPurchaseItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $assetPurchaseId,
        public readonly int $assetPurchaseItemId,
        public readonly string $serial,
    ) {
    }
}
