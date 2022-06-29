<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface BranchService
{
    public function create(array $branchArr): Branch;

    public function list(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(Branch $branch): Branch;

    public function update(
        Branch $branch,
        array $branchArr
    ): Branch;

    public function delete(Branch $branch): bool;

    public function getBranchByCompany(int $companyId = 0, Company $company = null): Collection;

    public function getMainBranchByCompany(int $companyId = 0, Company $company = null): Branch;

    public function resetMainBranch(int $companyId = 0, Company $company = null): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}