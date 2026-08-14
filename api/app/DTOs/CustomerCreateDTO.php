<?php

namespace App\DTOs;

final class CustomerCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly ?int $groupId,
        public readonly string $code,
        public readonly string $name,
        public readonly mixed $paymentTermType,
        public readonly int $paymentTerm,
        public readonly bool $taxableEnterprise,
        public readonly ?string $taxId,
        public readonly bool $isMember,
        public readonly int $maxOpenInvoice,
        public readonly int $maxInvoiceAge,
        public readonly int $maxOutstandingInvoice,
        public readonly mixed $zone,
        public readonly ?string $remarks,
        public readonly mixed $status,
    ) {
    }
}
