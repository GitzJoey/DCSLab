<?php

namespace App\DTOs;

final class SalesInvoiceUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $date,
        public readonly int $dueDays,
        public readonly int $customerId,
        public readonly int $salesOrderId,
        public readonly ?string $taxInvoiceNumber,
        public readonly float $taxInvoiceVatBase,
        public readonly float $taxInvoiceVat,
        public readonly ?string $remarks,
        public readonly bool $isPosted,
        public readonly float $globalDiscount,
        public readonly float $rounding,
        public readonly array $deleteItemIds,
        public readonly array $items,
        public readonly array $deletePaymentIds,
        public readonly array $payments,
    ) {
    }
}
