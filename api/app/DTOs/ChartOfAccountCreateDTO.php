<?php

namespace App\DTOs;

final class ChartOfAccountCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $scope,
        public readonly ?string $systemKey,
        public readonly ?int $parentId,
        public readonly ?string $sourceType,
        public readonly ?int $sourceId,
        public readonly string $code,
        public readonly string $name,
        public readonly string $normalBalance,
        public readonly bool $isGroup,
        public readonly bool $isActive,
        public readonly ?string $remarks,
    ) {
    }
}
