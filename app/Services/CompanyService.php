<?php

namespace App\Services;

use App\Models\Company;

interface CompanyService
{
    public function create(
        string $code,
        string $name,
        string $address,
        int $default,
        int $status,
        int $userId
    ) : Company;

    public function read(int $userId, string $search = '', bool $paginate = true, int $perPage = 10);

    public function getAllActiveCompany(int $userId);

    public function getCompanyById(int $companyId): Company;

    public function getDefaultCompany(int $userId): Company;

    public function update(
        int $id,
        string $code,
        string $name,
        string $address,
        int $default,
        int $status
    ) : Company;

    public function delete(int $userId, int $id): bool;

    public function generateUniqueCode(): string;

    public function isUniqueCode(string $code, int $userId, ?int $exceptId = null): bool;

    public function resetDefaultCompany(int $userId): bool;

    public function isDefaultCompany(int $companyId): bool;
}
