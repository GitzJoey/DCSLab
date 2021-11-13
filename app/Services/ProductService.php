<?php

namespace App\Services;

interface ProductService
{
    public function create(
        $company_id,
        $code,
        $group_id,
        $brand_id,
        $name,
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $is_use_serial,
        $product_type,
        $status,
        $product_units
    );

    public function read($userId);

    public function read_product();

    public function read_service();

    public function update(
        $id,
        $company_id,
        $code,
        $group_id,
        $brand_id,
        $name,
        $tax_status,
        $supplier_id,
        $remarks,
        $point,
        $is_use_serial,
        $product_type,
        $status,
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}