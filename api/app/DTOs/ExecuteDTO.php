<?php

namespace App\DTOs;

final class ExecuteDTO
{
    public function __construct(
        public readonly bool $useCache,
        public readonly ?ExecutePaginationDTO $pagination,
        public readonly ?ExecuteGetDTO $get,
    ) {
    }
}
