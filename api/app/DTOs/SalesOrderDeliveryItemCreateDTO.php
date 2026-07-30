<?php

namespace App\DTOs;

final class SalesOrderDeliveryItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $salesOrderDeliveryId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly ?string $remarks,
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
