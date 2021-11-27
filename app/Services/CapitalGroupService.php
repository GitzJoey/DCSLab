<?php

namespace App\Services;

interface CapitalGroupService
{
    public function create(
        $company_id,
        $code,
        $name
    );

    public function read($userId);

    public function getAllActiveCapitalGroup();

    public function update(
        $id,
        $company_id,
        $code,
        $name
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}