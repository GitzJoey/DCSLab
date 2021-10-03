<?php

namespace App\Services;

interface EmployeeService
{
    public function create(
        $company_id, $name, $email, $password, 
        $address, $city, $postal_code, $country, $tax_id, $ic_num, $img_path, $status, $remarks,
        $role_id
    );

    public function read($userId);

    public function update(
        $id,
        $company_id, 
        $name, $email, $password, 
        $address, $city, $postal_code, $country, $tax_id, $ic_num, $img_path, $status, $remarks
    );

    public function updateProfile(
        $id,
        $profile
    );

    public function getEmployeeByEmail($email);

    public function delete($id);
}
