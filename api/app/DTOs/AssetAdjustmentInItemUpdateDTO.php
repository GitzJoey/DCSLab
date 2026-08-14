<?php

namespace App\DTOs;

final class AssetAdjustmentInItemUpdateDTO
{
    public function __construct(
        public readonly int $assetId,
        public readonly float $qty,
        public readonly string $remarks,
        /** @var int[] */
        public readonly array $deleteSerialIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
