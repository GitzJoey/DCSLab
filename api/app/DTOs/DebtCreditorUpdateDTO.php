<?php

namespace App\DTOs;

final class DebtCreditorUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $remarks,
    ) {
    }
}
