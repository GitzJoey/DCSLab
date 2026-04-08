<?php

namespace App\DTOs;

final class PurchaseOrderItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseOrderId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitPrice,
        public readonly array $productUnitPriceDiscounts,
        public readonly array $subtotalDiscounts,
        public readonly bool $isVatIncluded,
        public readonly ?int $vatProfileId,
        public readonly float $vatRate,
        public readonly float $vatBaseFactor,
        public readonly ?string $remarks,
    ) {
    }
}
