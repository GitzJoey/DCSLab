<?php

namespace App\DTOs;

final class ExecuteGetDTO
{
    public function __construct(
        public readonly ?int $limit,
        // public readonly int $offset, // for next
    ) {
    }
}
