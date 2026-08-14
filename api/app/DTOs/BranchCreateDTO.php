<?php

namespace App\DTOs;

final class BranchCreateDTO
{
    public function __construct(
        public readonly int $companyId,
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
