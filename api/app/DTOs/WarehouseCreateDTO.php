<?php

namespace App\DTOs;

final class WarehouseCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $contact,
        public readonly ?string $remarks,
        public readonly int $status,
    ) {
    }
}
