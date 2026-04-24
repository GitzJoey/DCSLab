<?php

namespace App\DTOs;

final class PurchaseDirectCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly int $supplierId,
        public readonly ?int $purchaseOrderId,
        public readonly int $directReceiptWarehouseId,
        public readonly ?string $taxInvoiceNumber,
        public readonly float $taxInvoiceVatBase,
        public readonly float $taxInvoiceVat,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        public readonly float $additionalCost,
        public readonly float $rounding,

        public readonly array $items,

        public readonly array $globalDiscounts,

        public readonly array $additionalCosts,
    ) {
    }
}
