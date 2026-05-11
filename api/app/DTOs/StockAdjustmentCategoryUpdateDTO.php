<?php

namespace App\DTOs;

final class StockAdjustmentCategoryUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
    ) {
    }
}
