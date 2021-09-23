<?php

namespace App\Services;

interface EmployeeService
{
    public function create(
        $name,
        $email,
    );

    public function readAll();

    public function read($userId);

    public function update(
        $id,
        $name,
        $email,
    );    

    public function getEmployeeByEmail($email);

    public function delete($id);
}
