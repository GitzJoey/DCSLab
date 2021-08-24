<?php

namespace App\Services;

interface ProductService
{
    public function create(
        $code,
        $group_id,
        $brand_id,
        $name,
        $unit_id,
        $price,
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
        $code,
        $group_id,
        $brand_id,
        $name,
        $unit_id,
        $price,
        $tax_status,
        $remarks,
        $estimated_capital_price,
        $point,
        $is_use_serial,
        $product_type,
        $status
    );

    public function delete($id);
}