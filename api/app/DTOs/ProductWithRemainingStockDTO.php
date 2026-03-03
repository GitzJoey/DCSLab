<?php

namespace App\DTOs;

final class ProductWithRemainingStockDTO
{
    public function __construct(
        public readonly ?string $endDate,
        public readonly ?int $warehouseId,
        public readonly ?string $stockFilter,
        public readonly ?float $lessThan,
        public readonly ?float $greaterThan,
        public readonly ?bool $includeServiceProducts,
        public readonly ?string $sortByRemainingStock,
    ) {
    }
}
