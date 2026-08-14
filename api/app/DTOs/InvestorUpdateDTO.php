<?php

namespace App\DTOs;

final class InvestorUpdateDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $remarks,
    ) {
    }
}
