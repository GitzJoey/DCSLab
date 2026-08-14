<?php

namespace App\DTOs;

final class DebtCategoryUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly int $sequence,
    ) {
    }
}
