<?php

namespace App\Services;

interface SupplierService
{
    public function create(
        $company_id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    );

    public function read($userId);

    public function getAllSupplier();

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $term,
        $contact,
        $address,
        $city,
        $is_tax,
        $tax_number,
        $remarks,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}