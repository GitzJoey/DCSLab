<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface WarehouseService
{
    public function create(
        array $warehouseArr
    ): Warehouse;

    public function list(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(Warehouse $warehouse): Warehouse;

    public function update(
        Warehouse $warehouse,
        array $warehouseArr
    ): Warehouse;

    public function delete(Warehouse $warehouse): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}