<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface EmployeeService
{
    public function create(
        int $company_id,
        array $user,
        string $join_date,
        int $status
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
        int $company_id,
        int $user_id,
        int $status
    ): ?Employee;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}