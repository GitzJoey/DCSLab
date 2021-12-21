<?php

namespace App\Services;

use App\Models\Supplier;

interface SupplierService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        string $contact,
        string $address,
        string $city,
        bool $is_tax,
        string $tax_id,
        string $remarks,
        int $status,
        array $poc,
        array $products
    ): Supplier;

    public function read(int $companyId, string $search = '', bool $paginate = true, int $perPage = 10);

    public function update(
        int $id,
        string $code,
        string $name,
        string $term,
        string $contact,
        string $address,
        string $city,
        bool $is_tax,
        string $tax_id,
        string $remarks,
        int $status,
        array $poc,
        array $products
    ): Supplier;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}
