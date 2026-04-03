<?php

namespace App\DTOs;

use App\Models\StockTransferItem;

final class StockTransferItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockTransferId,
        public readonly int $stockTransferItemId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockTransferItem(StockTransferItem $stockTransferItem, string $serial): self
    {
        return new self(
            companyId: $stockTransferItem->company_id,
            branchId: $stockTransferItem->branch_id,
            stockTransferId: $stockTransferItem->stock_transfer_id,
            stockTransferItemId: $stockTransferItem->id,
            serial: $serial,
        );
    }
}
