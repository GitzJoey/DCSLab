<?php

namespace App\DTOs;

final class AssetSaleCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly int $customerId,
        public readonly string $remarks,
        public readonly bool $isPosted,
        public readonly string $rounding,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
    ) {
    }
}
