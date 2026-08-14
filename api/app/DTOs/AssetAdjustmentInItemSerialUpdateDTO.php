<?php

namespace App\DTOs;

final class AssetAdjustmentInItemSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }
}
