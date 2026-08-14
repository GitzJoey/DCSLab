<?php

namespace App\DTOs;

final class BranchUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $contact,
        public readonly bool $isMain,
        public readonly ?string $remarks,
        public readonly mixed $status,
    ) {
    }
}
