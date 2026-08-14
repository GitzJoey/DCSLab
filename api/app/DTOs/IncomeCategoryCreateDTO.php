<?php

namespace App\DTOs;

final class IncomeCategoryCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly ?int $parentId,
        public readonly string $code,
        public readonly string $name,
        public readonly int $sequence,
    ) {
    }
}
