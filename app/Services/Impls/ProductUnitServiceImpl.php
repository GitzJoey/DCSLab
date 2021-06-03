<?php

namespace App\Services\Impls;

use App\Services\ProductUnitService;
use App\Models\ProductUnit;

class ProductUnitServiceImpl implements ProductUnitService
{
    public function create(
        $code,
        $name
    )
    {


    }

    public function read()
    {
        return ProductUnit::where('code', '=', '001')->get();
    }


    public function update(
        $code,
        $name
    )
    {

        
    }


    public function delete($id)
    {

        
    }

}