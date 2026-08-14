<?php

namespace App\DTOs;

final class CashAccountUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly bool $isBank,
        public readonly bool $isActive,
        public readonly ?string $remarks,
    ) {
    }
}
