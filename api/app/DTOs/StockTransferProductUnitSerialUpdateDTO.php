<?php

namespace App\DTOs;

use App\Models\StockTransferProductUnitSerial;

final class StockTransferProductUnitSerialUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $stockTransferId,
        public readonly int $stockTransferProductUnitId,
        public readonly string $serial,
    ) {
    }

    public static function fromStockTransferProductUnitSerial(StockTransferProductUnitSerial $stockTransferProductUnitSerial, string $serial): self
    {
        return new self(
            companyId: $stockTransferProductUnitSerial->company_id,
            branchId: $stockTransferProductUnitSerial->branch_id,
            stockTransferId: $stockTransferProductUnitSerial->stock_transfer_id,
            stockTransferProductUnitId: $stockTransferProductUnitSerial->stock_transfer_product_unit_id,
            serial: $serial,
        );
    }
}
