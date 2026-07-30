<?php

namespace App\DTOs;

final class SalesOrderUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly ?int $customerId,
        public readonly ?string $remarks,
        public readonly float $globalDiscount,
        public readonly float $rounding,

        /** @var int[] */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,

        /** @var int[] */
        public readonly array $deletePaymentIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $payments,

        /** @var int[] */
        public readonly array $deleteRefundedPaymentIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $refundedPayments,
    ) {
    }
}
