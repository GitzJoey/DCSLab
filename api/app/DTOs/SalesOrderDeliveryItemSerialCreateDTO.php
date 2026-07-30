<?php

namespace App\DTOs;

final class SalesOrderDeliveryItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $salesOrderDeliveryId,
        public readonly int $salesOrderDeliveryItemId,
        public readonly string $serial,
    ) {
    }
}
