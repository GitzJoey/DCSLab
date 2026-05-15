<?php

namespace App\DTOs;

final class AssetSaleItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $assetSaleId,
        public readonly int $assetSaleItemId,
        public readonly string $serial,
    ) {
    }
}
