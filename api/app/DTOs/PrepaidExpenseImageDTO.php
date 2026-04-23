<?php

namespace App\DTOs;

final class PrepaidExpenseImageDTO
{
    public function __construct(
        public readonly string $hash,
        public readonly bool $isMain,
    ) {
    }
}
