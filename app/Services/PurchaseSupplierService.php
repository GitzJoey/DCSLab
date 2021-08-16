<?php

namespace App\Services;

interface PurchaseSupplierService
{
    public function create(
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

    public function read();

    public function update(
        $id,
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
}