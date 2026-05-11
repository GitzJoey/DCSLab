<?php

namespace App\DTOs;

final class PurchaseAdditionalCostUpdateDTO
{
    public function __construct(
        public readonly int $purchaseId,
        public readonly int $purchaseAdditionalCostCategoryId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly ?int $paidImmediatelyCashAccountId,
        public readonly float|int $amountPaidImmediately,
        public readonly float|int $amountPayable,
        public readonly ?string $remarks,
    ) {
    }
}
