<?php

namespace App\DTOs;

final class PurchaseOrderReceiptItemSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }
}
