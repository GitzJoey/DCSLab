<?php

namespace App\Services;

use App\Models\Branch;
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

    public function getBranchByCompanyId(int $companyId);

    public function getMainBranchByCompanyId(int $companyId): Branch;

    public function isMainBranch(int $id): bool;

    public function resetMainBranch(int $companyId): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}