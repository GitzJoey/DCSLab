<?php

namespace App\Services;

use App\Models\Product;

interface ProductService
{
    public function create(
        int $company_id,
        string $code,
        int $product_group_id,
        int $brand_id,
        string $name,
        bool $taxable_supplies,
        int $rate_supplies,
        bool $price_include_vat,
        string $remarks,
        int $point,
        bool $use_serial_number,
        bool $has_expiry_date,
        string $product_type,
        string $status,
        array $product_units
    ): ?Product;

    public function read(
        int $companyId,
        bool $isProduct = true, 
        bool $isService = true,
        string $search = '',
        bool $paginate = true,
        int $perPage = 10
    );

    public function update(
        int $id,
        int $company_id,
        string $code,
        int $product_group_id,
        int $brand_id,
        string $name,
        bool $taxable_supplies,
        int $rate_supplies,
        bool $price_include_vat,
        string $remarks,
        int $point,
        bool $use_serial_number,
        bool $has_expiry_date,
        string $product_type,
        string $status,
        array $product_units
    ): ?Product;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}