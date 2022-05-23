<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

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
        int $status,
    ): ?Branch;

    public function read(
        int $companyId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null;

    public function getBranchByCompanyId(int $companyId);

    public function update(
        int $id,
        int $company_id,
        string $code,
        string $name,
        ?string $address = null,
        ?string $city = null,
        ?string $contact = null,
        ?string $remarks = null,
        int $status,
    ): ?Branch;

    public function delete(int $id): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool;
}