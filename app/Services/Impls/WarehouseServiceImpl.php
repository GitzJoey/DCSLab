<?php

namespace App\Services\Impls;

use App\Services\WarehouseService;
use App\Models\Warehouse;

class WarehouseServiceImpl implements WarehouseService
{
    public function create(
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $is_active
    )
    {


    }

    public function read()
    {
        return Warehouse::get();
    }


    public function update(
        $company_id,
        $code,
        $name,
        $address,
        $city,
        $contact,
        $remarks,
        $is_active
    )
    {

        
    }


    public function delete($id)
    {

        
    }
}