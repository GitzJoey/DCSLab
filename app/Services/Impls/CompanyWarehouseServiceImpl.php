<?php

namespace App\Services\Impls;

use App\Services\CompanyWarehouseService;
use App\Models\CompanyWarehouse;

class CompanyWarehouseServiceImpl implements CompanyWarehouseService
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
        return CompanyWarehouse::get();
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