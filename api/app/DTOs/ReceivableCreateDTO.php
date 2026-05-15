<?php

namespace App\DTOs;

final class ReceivableCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $categoryId,
        public readonly int $customerId,
        public readonly ?int $cashAccountId,
        public readonly float $directAmountReceived,
        public readonly float $openingAmountDue,
        public readonly int $dueDays,
        public readonly ?string $remarks,

        /** @var array<int, array<string, mixed>> */
        public readonly array $payments,
    ) {
    }
}
