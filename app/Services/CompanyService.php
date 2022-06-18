<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface CompanyService
{
    public function create(
        string $code,
        string $name,
        ?string $address = null,
        bool $default,
        int $status,
        int $userId
    ): ?Company;

    public function read(
        int $userId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection|null;

    public function getAllActiveCompany(
        int $userId, 
        ?array $with = []
    );

    public function getCompanyById(int $companyId): Company;

    public function getDefaultCompany(int $userId): Company;

    public function update(
        int $id,
        string $code,
        string $name,
        ?string $address = null,
        bool $default,
        int $status
    ): ?Company;

    public function delete(int $userId, int $id): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $userId, ?int $exceptId = null): bool;

    public function resetDefaultCompany(int $userId): bool;

    public function isDefaultCompany(int $companyId): bool;
}
