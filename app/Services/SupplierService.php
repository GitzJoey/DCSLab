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
        ?int $payment_term = null,
        ?string $contact = null,
        ?string $address = null,
        ?string $city = null,
        bool $taxable_enterprise,
        string $tax_id,
        ?string $remarks = null,
        int $status,
        array $poc,
        array $products
    ): ?Supplier;

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10
    );

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        string $payment_term_type,
        ?int $payment_term = null,
        ?string $contact = null,
        ?string $address = null,
        ?string $city = null,
        bool $taxable_enterprise,
        string $tax_id,
        ?string $remarks = null,
        int $status,
        array $poc,
        array $products
    ): ?Supplier;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}
