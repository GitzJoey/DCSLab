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

        public readonly array $deleteImageIds,
        public readonly array $deletePaymentIds,
        public readonly array $images,
        public readonly array $payments,
    ) {
    }
}
