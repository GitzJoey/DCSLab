<?php

namespace App\DTOs;

final class UnitCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $type,
    ) {
    }
}
