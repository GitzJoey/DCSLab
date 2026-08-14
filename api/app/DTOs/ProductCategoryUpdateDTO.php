<?php

namespace App\DTOs;

final class ProductCategoryUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $type,
    ) {
    }
}
