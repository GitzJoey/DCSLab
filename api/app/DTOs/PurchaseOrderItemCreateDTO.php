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
        public readonly bool $productUnitIsPriceIncludeVat,
        public readonly array $productUnitPriceDiscounts,
        public readonly array $subtotalDiscounts,
        public readonly ?int $vatProfileId,
        public readonly float $vatRate,
        public readonly int $vatBaseNumerator,
        public readonly int $vatBaseDenominator,
        public readonly ?string $remarks,
    ) {
    }
}
