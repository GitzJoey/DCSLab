<?php

namespace App\Services;

use App\Models\ProductGroup;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface ProductGroupService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        int $category,
    ): ?ProductGroup;

    public function read(
        int $companyId,
        ?string $category = null, 
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null;

    public function readBy(string $key, string $value);

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        int $category
    ): ?ProductGroup;

    public function delete(int $id): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}