<?php

namespace App\DTOs;

use App\Models\StockTransferItemSerial;

final class StockTransferItemSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockTransferId,
        public readonly int $stockTransferItemId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockTransferItemSerial(StockTransferItemSerial $stockTransferItemSerial, string $serial): self
    {
        return new self(
            companyId: $stockTransferItemSerial->company_id,
            branchId: $stockTransferItemSerial->branch_id,
            stockTransferId: $stockTransferItemSerial->stock_transfer_id,
            stockTransferItemId: $stockTransferItemSerial->stock_transfer_item_id,
            serial: $serial,
        );
    }
}
