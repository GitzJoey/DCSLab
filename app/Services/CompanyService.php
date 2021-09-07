<?php

namespace App\Services;

interface CompanyService
{
    public function create(
        $code,
        $name,
        $default,
        $status
    );

    public function read($userId);

    public function getAllActiveCompany();

    public function update(
        $id,
        $code,
        $name,
        $default,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);

    public function resetDefaultCompany($userId);
}