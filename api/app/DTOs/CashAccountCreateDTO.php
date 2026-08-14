<?php

namespace App\DTOs;

final class CashAccountCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $name,
        public readonly bool $isBank,
        public readonly bool $isActive,
        public readonly ?string $remarks,
    ) {
    }
}
