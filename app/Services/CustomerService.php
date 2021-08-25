<?php

namespace App\Services;

interface CustomerService
{
    public function create(
        $code,
        $name,
        $customer_group_id,
        $sales_territory,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_age_notes,
        $limit_age_notes,
        $term,
        $address,
        $city,
        $contact,
        $tax_id,
        $remarks,
        $status
    );

    public function read();

    public function update(
        $id,
        $code,
        $name,
        $customer_group_id,
        $sales_territory,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_age_notes,
        $limit_age_notes,
        $term,
        $address,
        $city,
        $contact,
        $tax_id,
        $remarks,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($id, $code);
}
