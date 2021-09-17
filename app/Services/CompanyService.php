<?php

namespace App\Services;

interface CompanyService
{
    public function create(
        $code,
        $name,
        $default,
        $status,
        $userId
    );

    public function read($userId);

    public function getAllActiveCompany($userId);

    public function update(
        $id,
        $code,
        $name,
        $default,
        $status
    );

    public function delete($userId, $id);

    public function checkDuplicatedCode($crud_status, $id, $code);

    public function resetDefaultCompany($userId);

    public function isDefaultCompany($companyId);
}
