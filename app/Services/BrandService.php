<?php

namespace App\Services;

use App\Models\Brand;

interface BrandService
{
    public function create(
        int $company_id,
        string $code,
        string $name
    ): ?Brand;

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10, 
        bool $useCache = true
    );
    public function readBy(string $key, string $value);

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name
    ): ?Brand;

    public function delete(int $id): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}
