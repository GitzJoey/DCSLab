<?php

namespace App\DTOs;

final class UnitUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $type,
    ) {
    }
}
