<?php

namespace App\DTOs;

final class SalesReturnUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $customerId,
        public readonly ?int $salesInvoiceId,
        public readonly int $warehouseId,
        public readonly float $globalDiscount,
        public readonly float $rounding,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        public readonly array $deleteItemIds,
        public readonly array $items,
        public readonly array $deleteRefundIds,
        public readonly array $refunds,
    ) {
    }
}
