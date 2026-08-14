<?php

namespace App\DTOs;

final class SalesInvoiceCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
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
        public readonly array $items,
        public readonly array $payments,
    ) {
    }
}
