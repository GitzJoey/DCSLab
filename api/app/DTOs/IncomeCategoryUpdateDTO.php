<?php

namespace App\DTOs;

final class IncomeCategoryUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly int $sequence,
    ) {
    }
}
