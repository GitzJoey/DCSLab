<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface EmployeeService
{
    public function create(
        int $company_id,
        string $code,
        string $name,
        string $email,
        string $address,
        string $city,
        string $postal_code,
        string $country,
        string $tax_id,
        string $ic_num,
        ?string $img_path = null,
        string $join_date,
        string $remarks,
        int $status,
        array $accesses
    ): ?Employee;

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null;

    public function update(
        int $id,
        string $code,
        string $name,
        string $address,
        string $city,
        string $postal_code,
        string $country,
        string $tax_id,
        string $ic_num,
        ?string $img_path = null,
        ?string $join_date = null,
        string $remarks,
        int $status,
        array $accesses
    ): ?Employee;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}