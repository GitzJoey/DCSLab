<?php

namespace App\DTOs;

final class StockTransferCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $sourceWarehouseId,
        public readonly int $destinationWarehouseId,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        public readonly array $productUnits,
    ) {
    }
}
