<?php

namespace App\DTOs;

final class PrepaidExpenseCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $expenseCategoryId,
        public readonly int $estimatedUsefulLife,
        public readonly ?int $paidImmediatelyCashAccountId,
        public readonly float $amountPaidImmediately,
        public readonly float $amountPayable,
        public readonly int $dueDays,
        public readonly ?string $remarks,

        /** @var array<int, array<string, mixed>> */
        public readonly array $payments,
        /** @var array<int, array<string, mixed>> */
        public readonly array $images,
    ) {
    }
}
