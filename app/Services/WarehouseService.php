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
        $is_active
    );

    public function read();

    public function update(
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $is_active
    );

    public function delete($id);
}