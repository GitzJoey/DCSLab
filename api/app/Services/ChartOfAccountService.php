<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;

interface ChartOfAccountService
{
    public function create(
        array $chartOfAccountArr
    ): ChartOfAccount;

    public function createRootAccount(
        int $companyId
    ): array;

    public function list(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(ChartOfAccount $coaAsset): ChartOfAccount;

    public function readBy(string $key, string $value);

    public function update(
        ChartOfAccount $coaAsset,
        array $chartOfAccountArr
    ): ChartOfAccount;

    public function delete(ChartOfAccount $coaAsset): bool;

    public function generateUniqueCode(int $companyId, ?int $parentId): string;

    public function isUniqueCode(int $parentId, string $code, int $companyId, ?int $exceptId = null): bool;
}
