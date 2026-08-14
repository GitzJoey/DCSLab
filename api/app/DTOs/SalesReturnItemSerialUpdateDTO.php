<?php

namespace App\DTOs;

final class SalesReturnItemSerialUpdateDTO
{
    public function __construct(
        public readonly string $serial,
    ) {
    }
}
