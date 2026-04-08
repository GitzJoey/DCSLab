<?php

namespace App\DTOs;

final class PurchaseOrderDownPaymentUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $cashAccountId,
        public readonly float $amount,
        public readonly ?string $remarks,
    ) {
    }
}
