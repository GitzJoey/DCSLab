<?php

namespace App\DTOs;

final class WarehouseUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $contact,
        public readonly ?string $remarks,
        public readonly mixed $status,
    ) {
    }
}
