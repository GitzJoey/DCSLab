<?php

namespace App\DTOs;

final class AssetPurchaseCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly int $supplierId,
        public readonly string $remarks,
        public readonly bool $isPosted,
        public readonly string $additionalCost,
        public readonly string $rounding,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
    ) {
    }
}
