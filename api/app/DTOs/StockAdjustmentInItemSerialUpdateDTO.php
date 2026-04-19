<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInItemSerial;

final class StockAdjustmentInItemSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentInItemSerial(StockAdjustmentInItemSerial $stockAdjustmentInItemSerial, string $serial): self
    {
        return new self(
            serial: $serial,
        );
    }
}
