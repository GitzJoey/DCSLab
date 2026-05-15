<?php

namespace App\DTOs;

final class PurchaseDirectUpdateDTO
{
    public function __construct(
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
        public readonly float $rounding,

        /** @var int[] */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> */
        public array $items,

        /** @var int[] */
        public readonly array $deleteGlobalDiscountIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $globalDiscounts,

        /** @var int[] */
        public readonly array $deleteAdditionalCostIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $additionalCosts,
    ) {
    }
}
