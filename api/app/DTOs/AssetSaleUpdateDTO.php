<?php

namespace App\DTOs;

final class AssetSaleUpdateDTO
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
        /** @var int[] */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
    ) {
    }
}
