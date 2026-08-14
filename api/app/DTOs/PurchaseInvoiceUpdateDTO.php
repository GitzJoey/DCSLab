<?php

namespace App\DTOs;

final class PurchaseInvoiceUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly int $supplierId,
        public readonly int $purchaseOrderId,
        public readonly ?string $taxInvoiceNumber,
        public readonly float $taxInvoiceVatBase,
        public readonly float $taxInvoiceVat,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        public readonly float $globalDiscount,
        public readonly float $rounding,
        /** @var array<int, int> */
        public readonly array $deleteItemIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $items,
        /** @var array<int, int> */
        public readonly array $deletePaymentIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $payments,
    ) {
    }
}
