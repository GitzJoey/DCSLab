<?php

namespace App\DTOs;

final class DebtCategoryCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
        public readonly int $sequence,
    ) {
    }
}
