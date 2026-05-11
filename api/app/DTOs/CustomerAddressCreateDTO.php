<?php

namespace App\DTOs;

final class CustomerAddressCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $contact,
        public readonly bool $isMain,
        public readonly ?string $remarks,
    ) {
    }
}
