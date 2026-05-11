<?php

namespace App\DTOs;

final class SupplierCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly mixed $paymentTermType,
        public readonly int $paymentTerm,
        public readonly bool $taxableEnterprise,
        public readonly ?string $taxId,
        public readonly ?string $remarks,
        public readonly mixed $status,
    ) {
    }
}
