<?php

namespace App\DTOs;

final class LiabilityUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $categoryId,
        public readonly ?int $creditorId,
        public readonly ?int $supplierId,
        public readonly ?int $cashAccountId,
        public readonly float $amountReceived,
        public readonly float $amountPayable,
        public readonly int $dueDays,
        public readonly ?string $remarks,

        public readonly array $deletePaymentIds,
        public readonly array $payments,
    ) {
    }
}
