<?php

namespace App\DTOs;

final class AssetSaleItemSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $serial,
    ) {
    }
}
