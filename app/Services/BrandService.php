<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface BrandService
{
    public function create(
        array $brandArr
    ): Brand;

    public function list(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;
    
    public function read(Brand $brand): Brand;

    public function readBy(string $key, string $value);

    public function update(
        Brand $brand,
        array $brandArr
    ): Brand;

    public function delete(Brand $brand): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}