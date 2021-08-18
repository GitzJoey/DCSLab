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
        $tax_status ,
        $information,
        $estimated_capital_price,
        $is_use_serial,
        $is_buy,
        $is_production_material,
        $is_production_result,
        $is_sell,
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
        $tax_status ,
        $information,
        $estimated_capital_price,
        $is_use_serial,
        $is_buy,
        $is_production_material,
        $is_production_result,
        $is_sell,
        $status
    );

    public function delete($id);
}