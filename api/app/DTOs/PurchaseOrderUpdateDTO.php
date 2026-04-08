<?php

namespace App\DTOs;

final class PurchaseOrderUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly ?int $supplierId,
        public readonly ?string $remarks,
        public readonly array $deleteGlobalDiscountIds,
        public readonly array $globalDiscounts,

        public readonly array $deleteItemIds,
        public readonly array $items,
        public readonly array $deleteDownPaymentIds,
        public readonly array $downPayments,
    ) {
    }
}
