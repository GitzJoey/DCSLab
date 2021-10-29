<?php

namespace App\Services;

interface ExpenseGroupService
{
    public function create(
        $company_id,
        $code,
        $name,
    );

    public function read();

    public function update(
        $id,
        $company_id,
        $code,
        $name,
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}