<?php

namespace App\DTOs;

final class VatProfileCreateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
        public readonly float|int $vatRate,
        public readonly float|int $vatBaseNumerator,
        public readonly float|int $vatBaseDenominator,
        public readonly ?string $remarks,
        public readonly bool $isActive,
    ) {
    }
}
