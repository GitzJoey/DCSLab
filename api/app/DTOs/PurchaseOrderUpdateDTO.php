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

        /** @var int[] */
        public readonly array $deleteGlobalDiscountIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $globalDiscounts,

        /** @var int[] */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,

        /** @var int[] */
        public readonly array $deleteDownPaymentIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $downPayments,

        /** @var int[] */
        public readonly array $deleteRefundedDownPaymentIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $refundedDownPayments,
    ) {
    }
}
