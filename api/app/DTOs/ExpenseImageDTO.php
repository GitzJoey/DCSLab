<?php

namespace App\DTOs;

final class ExpenseImageDTO
{
    public function __construct(
        public readonly string $hash,
        public readonly bool $isMain,
    ) {
    }
}
