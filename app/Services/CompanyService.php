<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface CompanyService
{
    public function create(
        array $companyArr
    ): Company;

    public function list(
        int $userId,
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(Company $company): Company;

    public function update(
        Company $company,
        array $companyArr
    ): Company;

    public function delete(Company $company): bool;

    public function getAllActiveCompany(
        int $userId, 
        ?array $with = []
    );

    public function getCompanyById(int $companyId): Company;

    public function getDefaultCompany(User $user): Company;

    public function resetDefaultCompany(User $user): bool;

    public function isDefaultCompany(Company $company): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $userId, ?int $exceptId = null): bool;
}
