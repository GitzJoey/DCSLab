<?php

namespace App\DTOs;

final class IncomeUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $incomeCategoryId,
        public readonly ?int $paidImmediatelyCashAccountId,
        public readonly float $amountPaidImmediately,
        public readonly float $amountReceivable,
        public readonly int $dueDays,
        public readonly ?string $remarks,

        /** @var int[] */
        public readonly array $deleteImageIds,
        /** @var int[] */
        public readonly array $deletePaymentIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $images,
        /** @var array<int, array<string, mixed>> */
        public readonly array $payments,
    ) {
    }
}
