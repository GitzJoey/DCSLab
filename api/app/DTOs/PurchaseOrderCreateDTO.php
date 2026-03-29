<?php

namespace App\DTOs;

final class PurchaseOrderCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $supplierId,
        public readonly string $code,
        public readonly string $date,
        public readonly ?string $shippingDate,
        public readonly ?string $shippingAddress,
        public readonly ?string $remarks,
        public readonly bool $isHasInvoice,
        public readonly bool $isReceived,
        public readonly float $total,
        public readonly float $globalDiscountRate,
        public readonly float $globalDiscountFixed,
        public readonly float $grandTotal,
        public readonly float $downPayment,
        public readonly int $downPaymentDueDays,
        public readonly float $downPaymentApplied,
        public readonly float $downPaymentRemaining,
        public readonly bool $isDownPaymentPaidOff,
        public readonly array $productUnits,
        public readonly array $downPayments,
    ) {
    }
}
