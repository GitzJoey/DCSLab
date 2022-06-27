<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface WarehouseService
{
    public function create(
        int $company_id,
        int $branch_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        int $status,
    ): ?Warehouse;

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null;

    public function update(
        int $id,
        int $company_id,
        int $branch_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        int $status,
    ): ?Warehouse;

    public function delete(int $id): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}