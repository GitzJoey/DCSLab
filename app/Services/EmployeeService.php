<?php

namespace App\Services;

interface EmployeeService
{
    public function create(
        $company_id, 
        $name, 
        $email, 
        $rolesId, 
        $profile
    );

    public function read($userId);

    public function update(
        $id,
        $company_id, 
        $name, 
        $email, 
        $rolesId, 
        $profile
    );

    public function updateProfile(
        $id,
        $profile
    );

    public function getEmployeeByEmail($email);

    public function delete($id);
}
