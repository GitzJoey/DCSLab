<?php

namespace App\DTOs;

final class ExecutePaginationDTO
{
    public readonly int $page;

    public readonly int $perPage;

    public function __construct(?int $page, ?int $perPage)
    {
        $this->page = max(1, abs((int) ($page ?? 1)));
        $this->perPage = max(1, abs((int) ($perPage ?? 10)));
    }
}
