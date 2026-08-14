<?php

namespace App\DTOs;

final class PurchaseReturnUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $supplierId,
        public readonly ?int $purchaseInvoiceId,
        public readonly int $warehouseId,
        public readonly float $globalDiscount,
        public readonly float $rounding,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        /** @var array<int, int> */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
        /** @var array<int, int> */
        public readonly array $deleteRefundIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $refunds,
    ) {
    }
}
