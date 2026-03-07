<?php

namespace App\DTOs;

final class CashAccountWithRemainingBalanceDTO
{
    public function __construct(
        public readonly ?string $endDate,
    ) {
    }
}
