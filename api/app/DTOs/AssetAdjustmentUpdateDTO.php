<?php

namespace App\DTOs;

final class AssetAdjustmentUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly string $remarks,
        public readonly bool $isPosted,
        /** @var int[] */
        public readonly array $deleteInItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $inItems,
        /** @var int[] */
        public readonly array $deleteOutItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $outItems,
    ) {
    }
}
