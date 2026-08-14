<?php

namespace App\DTOs;

final class SalesOrderDeliveryUpdateDTO
{
    public function __construct(
        public readonly int $customerId,
        public readonly int $salesOrderId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $warehouseId,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        /** @var array<int, int> */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
        /** @var array<int, int> */
        public readonly array $deleteCostIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $costs,
    ) {
    }
}
