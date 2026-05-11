<?php

namespace App\DTOs;

final class PurchaseAdditionalCostCategoryUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
    ) {
    }
}
