<?php

namespace App\DTOs;

final class ExpenseCategoryUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly int $sequence,
    ) {
    }
}
