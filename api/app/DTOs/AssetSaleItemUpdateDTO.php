<?php

namespace App\DTOs;

final class AssetSaleItemUpdateDTO
{
    public function __construct(
        public readonly int $assetId,
        public readonly string $qty,
        public readonly string $unitPrice,
        public readonly string $remarks,
        /** @var int[] */
        public readonly array $deleteSerialIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
