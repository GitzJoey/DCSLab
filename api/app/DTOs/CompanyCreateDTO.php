<?php

namespace App\DTOs;

final class CompanyCreateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $address,
        public readonly bool $default,
        public readonly mixed $status,
    ) {
    }
}
