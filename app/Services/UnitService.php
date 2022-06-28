<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface UnitService
{
    public function create(
        array $unitArr
    ): Unit;

    public function list(
        int $companyId,
        ?string $category = null, 
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection;
    
    public function read(Unit $unit): Unit;

    public function readBy(string $key, string $value);

    public function update(
        Unit $unit,
        array $unitArr
    ): Unit;

    public function delete(Unit $unit): bool;
    
    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}