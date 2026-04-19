<?php

namespace App\DTOs;

final class PurchaseReceiptUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $warehouseId,
        public readonly ?string $remarks,
        public readonly bool $isPosted,

        public readonly array $items,
    ) {
    }
}
