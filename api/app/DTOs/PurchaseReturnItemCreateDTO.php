<?php

namespace App\DTOs;

final class PurchaseReturnItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $purchaseReturnId,
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
        /** @var array<int, array<string, mixed>> */
        public readonly array $serials,
    ) {
    }
}
