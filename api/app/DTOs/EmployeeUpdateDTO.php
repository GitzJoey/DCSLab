<?php

namespace App\DTOs;

final class EmployeeUpdateDTO
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $remarks,
    ) {
    }
}
