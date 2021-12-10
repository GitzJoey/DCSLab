<?php

namespace App\Services;

interface SupplierService
{
    public function create(
        $company_id,
        $code,
        $name,
        $payment_term_type,
        $contact,
        $address,
        $city,
        $taxable_enterprise,
        $tax_id,
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
        $payment_term_type,
        $contact,
        $address,
        $city,
        $taxable_enterprise,
        $tax_id,
        $remarks,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}