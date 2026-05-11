<?php

namespace App\DTOs;

final class CustomerGroupCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
        public readonly mixed $paymentTermType,
        public readonly int $paymentTerm,
        public readonly bool $sellAtCost,
        public readonly string $sellingPoint,
        public readonly string $sellingPointMultiple,
        public readonly float|int $priceMarkupPercent,
        public readonly float|int $priceMarkupNominal,
        public readonly float|int $priceMarkdownPercent,
        public readonly float|int $priceMarkdownNominal,
        public readonly mixed $roundingType,
        public readonly int $roundingDigit,
        public readonly int $maxOpenInvoice,
        public readonly int $maxInvoiceAge,
        public readonly int $maxOutstandingInvoice,
        public readonly ?string $remarks,
    ) {
    }
}
