<?php

namespace App\Services;

use App\Models\Branch;

interface BranchService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        ?string $status = null,
    ): ?Branch;

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        ?int $perPage = 10
    );

    public function readBy(string $key, string $value);

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        ?string $status = null,
    ): ?Branch;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}