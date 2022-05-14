<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

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
        int $page = 1,
        int $perPage = 10, 
        bool $useCache = true
    ): Paginator|Collection|null;
    
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
