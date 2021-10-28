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
        $remarks,
        $estimated_capital_price,
        $point,
        $is_use_serial,
        $product_type,
        $status

    );

    public function read();

    public function update(
        $id,
        $company_id,
        $code,
        $group_id,
        $brand_id,
        $name,
        $tax_status,
        $remarks,
        $estimated_capital_price,
        $point,
        $is_use_serial,
        $product_type,
        $status
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}