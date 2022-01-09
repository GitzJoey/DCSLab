<?php

namespace App\Services;

interface CustomerService
{
    public function create(
        $company_id,
        $code,
        $name,
        $customer_group_id,
        $sales_territory,
        $max_open_invoice,
        $max_outstanding_invoice,
        $max_invoice_age,
        $payment_term,
        $tax_id,
        $remarks,
        $status,
        $customer_addresses,
    );

    public function read();

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $customer_group_id,
        $sales_territory,
        $max_open_invoice,
        $max_outstanding_invoice,
        $max_invoice_age,
        $payment_term,
        $tax_id,
        $remarks,
        $status,
        $customer_addresses
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}