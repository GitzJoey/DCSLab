<?php

namespace App\DTOs;

use App\Models\StockTransferProductUnit;

final class StockTransferProductUnitSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockTransferId,
        public readonly int $stockTransferProductUnitId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockTransferProductUnit(StockTransferProductUnit $stockTransferProductUnit, string $serial): self
    {
        return new self(
            companyId: $stockTransferProductUnit->company_id,
            branchId: $stockTransferProductUnit->branch_id,
            stockTransferId: $stockTransferProductUnit->stock_transfer_id,
            stockTransferProductUnitId: $stockTransferProductUnit->id,
            serial: $serial,
        );
    }
}
