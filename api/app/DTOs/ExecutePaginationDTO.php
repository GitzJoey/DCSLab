<?php

namespace App\DTOs;

final class ExecutePaginationDTO
{
    public function __construct(
        public readonly ?int $page,
        public readonly ?int $perPage,
    ) {
    }
}
