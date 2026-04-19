<?php

namespace App\DTOs;

final class PurchaseReceiptItemSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }
}
