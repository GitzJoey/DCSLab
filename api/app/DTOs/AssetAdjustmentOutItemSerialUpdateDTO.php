<?php

namespace App\DTOs;

final class AssetAdjustmentOutItemSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $serial,
    ) {
    }
}
