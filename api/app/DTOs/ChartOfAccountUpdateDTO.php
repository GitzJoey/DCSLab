<?php

namespace App\DTOs;

final class ChartOfAccountUpdateDTO
{
    public function __construct(
        public readonly ?int $parentId,
        public readonly string $code,
        public readonly string $name,
        public readonly string $normalBalance,
        public readonly bool $isGroup,
        public readonly bool $isActive,
        public readonly ?string $remarks,
    ) {
    }
}
