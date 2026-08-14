<?php

namespace App\DTOs;

final class ReceivablePaymentUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $cashAccountId,
        public readonly float|int $amount,
        public readonly ?string $remarks,
    ) {
    }
}
