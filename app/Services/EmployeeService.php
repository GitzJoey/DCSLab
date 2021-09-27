<?php

namespace App\Services;

interface EmployeeService
{
    public function create(
        $company_id,
        $name,
        $email,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    );

    public function readAll();

    public function read($userId);

    public function update(
        $id,
        $company_id,
        $name,
        $email,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    );    

    public function getEmployeeByEmail($email);

    public function delete($id);
}
