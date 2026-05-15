<?php

namespace App\DTOs;

final class AssetPurchaseItemSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $serial,
    ) {
    }
}
