<?php

namespace App\Services;

interface WarehouseService
{
    public function create(
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    );

    public function read($userId);

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}