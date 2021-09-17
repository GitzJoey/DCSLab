<?php

namespace App\Services;

interface EmployeeService
{
    public function create(
        $name,
        $email,
    );

    public function read();

    public function getEmployeeByEmail($email);

    public function update(
        $id,
        $name,
        $email,
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}
