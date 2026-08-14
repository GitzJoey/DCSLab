<?php

namespace App\DTOs;

final class CustomerAddressUpdateDTO
{
    public function __construct(
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $contact,
        public readonly bool $isMain,
        public readonly ?string $remarks,
    ) {
    }
}
