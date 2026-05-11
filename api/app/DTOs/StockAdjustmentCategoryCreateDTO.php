<?php

namespace App\DTOs;

final class StockAdjustmentCategoryCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
    ) {
    }
}
