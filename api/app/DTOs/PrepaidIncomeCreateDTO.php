<?php

namespace App\DTOs;

final class PrepaidIncomeCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $incomeCategoryId,
        public readonly int $estimatedUsefulLife,
        public readonly ?int $paidImmediatelyCashAccountId,
        public readonly float $amountPaidImmediately,
        public readonly float $amountReceivable,
        public readonly int $dueDays,
        public readonly ?string $remarks,

        /** @var array<int, array<string, mixed>> */
        public readonly array $payments,
        /** @var array<int, array<string, mixed>> */
        public readonly array $images,
    ) {
    }
}
