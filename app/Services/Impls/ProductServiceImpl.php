<?php

namespace App\Services\Impls;

use App\Services\ProductService;
use App\Models\Product;

class ProductServiceImpl implements ProductService
{
    public function create(
        $code,
        $group_id,
        $brand_id,
        $name,
        $unit_id,
        $price,
        $tax ,
        $information,
        $estimated_capital_price,
        $is_use_serial,
        $is_buy,
        $is_production_material,
        $is_production_result,
        $is_sell,
        $status
    )
    {


    }

    public function read()
    {
        return Product::with('group', 'brand', 'unit')->paginate();
        
    }

    public function update(
        $code,
        $group_id,
        $brand_id,
        $name,
        $unit_id,
        $price,
        $tax ,
        $information,
        $estimated_capital_price,
        $is_use_serial,
        $is_buy,
        $is_production_material,
        $is_production_result,
        $is_sell,
        $status
    )
    {

        
    }


    public function delete($id)
    {

        
    }
}