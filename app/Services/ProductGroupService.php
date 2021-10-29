<?php

namespace App\Services;

interface ProductGroupService
{
    public function create(
        $company_id,
        $code,
        $name
    );

    public function read();

    public function getAllActiveProductGroup();

    public function update(
        $id,
        $company_id,
        $code,
        $name
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
