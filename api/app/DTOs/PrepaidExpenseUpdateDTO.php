<?php

namespace App\DTOs;

final class PrepaidExpenseUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $expenseCategoryId,
        public readonly int $estimatedUsefulLife,
        public readonly ?int $paidImmediatelyCashAccountId,
        public readonly float $amountPaidImmediately,
        public readonly float $amountPayable,
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
