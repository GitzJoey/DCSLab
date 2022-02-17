<?php

namespace App\Services;

use App\Models\Employee;

interface EmployeeService
{
    public function create(
        int $company_id,
        int $userId,
    ): ?Employee;

    public function read(
        int $companyId,
        int $userId,
        string $search = '',
        bool $paginate = true,
        int $perPage = 10
    );

    public function update(
        int $id,
        int $company_id,
        int $userId,
    ): ?Employee;

    public function delete(int $id): bool;

    public function generateUniqueCode(int $companyId): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}