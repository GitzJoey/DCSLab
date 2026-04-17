<?php

namespace App\DTOs;

final class PurchaseOrderUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly ?int $supplierId,
        public readonly ?string $remarks,
        public readonly float $rounding,

        public readonly array $deleteGlobalDiscountIds,
        public readonly array $globalDiscounts,

        public readonly array $deleteItemIds,
        public readonly array $items,

        public readonly array $deleteDownPaymentIds,
        public readonly array $downPayments,

        public readonly array $deleteRefundedDownPaymentIds,
        public readonly array $refundedDownPayments,
    ) {
    }
}
