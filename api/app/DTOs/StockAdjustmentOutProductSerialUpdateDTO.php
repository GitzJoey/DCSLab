<?php

namespace App\DTOs;

final class StockAdjustmentOutProductSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }
}
