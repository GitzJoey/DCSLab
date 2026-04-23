<?php

namespace App\DTOs;

final class ExpenseCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $expenseCategoryId,
        public readonly ?int $paidImmediatelyCashAccountId,
        public readonly float $amountPaidImmediately,
        public readonly float $amountPayable,
        public readonly int $dueDays,
        public readonly ?string $remarks,

        public readonly array $payments,
        public readonly array $images,
    ) {
    }
}
