<?php

namespace App\Services\Impls;

use App\Services\ProductBrandService;
use App\Models\ProductBrand;

class ProductBrandServiceImpl implements ProductBrandService
{
    public function create(
        $code,
        $name
    )
    {


    }

    public function read()
    {
        return ProductBrand::where('code', '=', '001')->get();
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