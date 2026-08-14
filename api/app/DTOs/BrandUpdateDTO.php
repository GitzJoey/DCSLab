<?php

namespace App\DTOs;

final class BrandUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
    ) {
    }
}
