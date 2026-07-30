<?php

namespace App\DTOs;

final class SalesReturnItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $salesReturnId,
        public readonly int $salesReturnItemId,
        public readonly string $serial,
    ) {
    }
}
