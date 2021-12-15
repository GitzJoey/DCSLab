<?php

namespace App\Services;

interface ProductService
{
    public function create(
        $company_id,
        $code,
        $product_group_id,
        $brand_id,
        $name,
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $use_serial_number,
        $has_expiry_date,
        $product_type,
        $status,
        $product_units
    );

    public function read();

    public function read_product();

    public function read_service();

    public function update(
        $id,
        $company_id,
        $code,
        $product_group_id,
        $brand_id,
        $name,
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $use_serial_number,
        $has_expiry_date,
        $product_type,
        $status,
        $product_units
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}