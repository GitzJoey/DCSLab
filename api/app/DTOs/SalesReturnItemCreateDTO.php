<?php

namespace App\DTOs;

final class SalesReturnItemCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $branchId,
        public readonly int $salesReturnId,
        public readonly ?int $salesOrderDeliveryItemId,
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
        public readonly array $serials,
    ) {
    }
}
