<?php

namespace App\DTOs;

final class StockAdjustmentCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $categoryId,
        public readonly ?int $inWarehouseId,
        public readonly ?int $outWarehouseId,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        /** @var array<int, array<string, mixed>> */
        public readonly array $inItems,
        /** @var array<int, array<string, mixed>> */
        public readonly array $outItems,
    ) {
    }
}
