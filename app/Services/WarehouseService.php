<?php

namespace App\Services;

use App\Models\Warehouse;

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
        int $page,
        int $perPage = 10
    );

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

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}