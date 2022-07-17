<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface SupplierService
{
    public function create(
        array $supplierArr,
        array $picArr,
        array $productsArr
    ): Supplier;

    public function list(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(Supplier $supplier): Supplier;

    public function update(
        Supplier $supplier,
        array $supplierArr,
        array $picArr,
        array $productsArr
    ): Supplier;

    public function delete(Supplier $supplier): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}
