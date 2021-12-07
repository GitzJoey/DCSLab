<?php

namespace App\Services;

use App\Models\Brand;

interface BrandService
{
    public function create(
        int $company_id,
        string $code,
        string $name
    ): Brand;

    public function read(int $companyId, string $search = '', bool $paginate = true, int $perPage = 10);

    public function readBy(string $key, string $value);

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name
    ): Brand;

    public function delete(int $id): bool;

    public function isUniqueCode(string $code, int $userId, int $exceptId): string;
}
