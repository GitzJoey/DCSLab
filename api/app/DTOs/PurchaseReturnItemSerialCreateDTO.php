<?php

namespace App\DTOs;

final class PurchaseReturnItemSerialCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseReturnId,
        public readonly int $purchaseReturnItemId,
        public readonly string $serial,
    ) {
    }
}
