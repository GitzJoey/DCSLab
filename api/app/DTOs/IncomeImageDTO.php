<?php

namespace App\DTOs;

final class IncomeImageDTO
{
    public function __construct(
        public readonly string $hash,
        public readonly bool $isMain,
    ) {
    }
}
