<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface ProductService
{
    public function create(
        array $productArr,
        array $productUnitsArr
    ): Product;

    public function list(
        int $companyId,
        bool $isProduct = true,
        bool $isService = true,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        ?int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(Product $product): Product;

    public function update(
        Product $product,
        array $productArr,
        array $productUnitsArr
    ): Product;

    public function delete(Product $product): bool;

    public function generateUniqueCodeForProduct(): string;

    public function generateUniqueCodeForProductUnits(): string;

    public function isUniqueCodeForProduct(string $code, int $companyId, ?int $exceptId = null): bool;

    public function isUniqueCodeForProductUnits(string $code, int $companyId, ?int $exceptId = null): bool;
}
