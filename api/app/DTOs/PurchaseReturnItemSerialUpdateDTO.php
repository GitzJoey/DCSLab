<?php

namespace App\DTOs;

final class PurchaseReturnItemSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }
}
