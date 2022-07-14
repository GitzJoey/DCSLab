<?php

namespace App\Services;

use App\Models\ProductGroup;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface ProductGroupService
{
    public function create(array $productGroupArr): ProductGroup;

    public function list(
        int $companyId,
        ?int $category,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(ProductGroup $productgroup): ProductGroup;

    public function readBy(string $key, string $value);

    public function update(
        ProductGroup $productgroup,
        array $productGroupArr
    ): ProductGroup;

    public function delete(ProductGroup $productgroup): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}