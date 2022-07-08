<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface EmployeeService
{
    public function create(
        array $employeeArr,
        array $userArr,
        array $profileArr,
        array $accessesArr
    ): Employee;

    public function list(
        int $companyId,
        string $search,
        bool $paginate,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(Employee $employee): Employee;

    public function update(
        Employee $employee,
        array $employeeArr,
        array $userArr,
        array $profileArr,
        array $accessesArr
    ): Employee;

    public function delete(Employee $employee): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}
