<?php

namespace App\DTOs;

final class PurchaseReturnItemUpdateDTO
{
    public function __construct(
        public readonly ?int $purchaseOrderReceiptItemId,
        public readonly float $qty,
        public readonly int $productUnitId,
        public readonly float $productUnitConversionValue,
        public readonly float $productUnitPrice,
        public readonly bool $productUnitIsPriceIncludeVat,
        public readonly float $priceDiscount,
        public readonly float $subtotalDiscount,
        public readonly ?int $vatProfileId,
        public readonly float $vatRate,
        public readonly int $vatBaseNumerator,
        public readonly int $vatBaseDenominator,
        public readonly ?string $remarks,
        /** @var array<int, int> */
        public readonly array $deleteSerialIds,
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
