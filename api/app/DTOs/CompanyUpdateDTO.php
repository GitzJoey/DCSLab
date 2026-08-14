<?php

namespace App\DTOs;

final class CompanyUpdateDTO
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
