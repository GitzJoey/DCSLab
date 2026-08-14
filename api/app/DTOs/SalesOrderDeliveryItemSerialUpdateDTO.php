<?php

namespace App\DTOs;

final class SalesOrderDeliveryItemSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }
}
