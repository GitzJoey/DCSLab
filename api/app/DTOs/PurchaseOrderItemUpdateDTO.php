<?php

namespace App\DTOs;

final class PurchaseOrderItemUpdateDTO
{
    public function __construct(
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitPrice,
        public readonly array $deleteProductUnitPriceDiscountIds,
        public readonly array $productUnitPriceDiscounts,
        public readonly array $deleteSubtotalDiscountIds,
        public readonly array $subtotalDiscounts,
        public readonly bool $isVatIncluded,
        public readonly ?int $vatProfileId,
        public readonly float $vatRate,
        public readonly int $vatBaseNumerator,
        public readonly int $vatBaseDenominator,
        public readonly ?string $remarks,
    ) {
    }
}
