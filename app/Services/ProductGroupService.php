<?php

namespace App\Services;

use App\Models\ProductGroup;

interface ProductGroupService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $category,
    ): ?ProductGroup;

    public function read(
        int $companyId,
        string $category,
        string $search = '',
        bool $paginate = true,
        int $page,
        ?int $perPage = 10
    );

    public function readBy(string $key, string $value);

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        string $category
    ): ?ProductGroup;

    public function delete(int $id): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}
