<?php

namespace App\DTOs;

final class PurchaseItemUpdateDTO
{
    public function __construct(
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitPrice,
        public readonly bool $productUnitIsPriceIncludeVat,
        /** @var int[] */
        public readonly array $deleteProductUnitPriceDiscountIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $productUnitPriceDiscounts,
        /** @var int[] */
        public readonly array $deleteSubtotalDiscountIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $subtotalDiscounts,
        public readonly ?int $vatProfileId,
        public readonly float $vatRate,
        public readonly int $vatBaseNumerator,
        public readonly int $vatBaseDenominator,
        public readonly ?string $remarks,
    ) {
    }
}
