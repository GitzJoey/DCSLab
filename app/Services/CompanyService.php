<?php

namespace App\Services;

interface CompanyService
{
    public function create(
        $code,
        $name,
        $status
    );

    public function read();

    public function getAllActiveCompany();

    public function update(
        $id,
        $code,
        $name,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($id, $code);
}