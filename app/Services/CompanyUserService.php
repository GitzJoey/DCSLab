<?php

namespace App\Services;

interface CompanyUserService
{
    public function create(
        $user_id,
        $company_id
    );

    public function read($user_id);

    public function getAllCompanyUser($user_id);

    public function update(
        $id,
        $user_id,
        $company_id
    );

    public function delete($id);
}
