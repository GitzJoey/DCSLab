<?php

namespace App\DTOs;

final class PrepaidIncomeImageDTO
{
    public function __construct(
        public readonly string $hash,
        public readonly bool $isMain,
    ) {
    }
}
