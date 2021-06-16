<?php

namespace App\Services\Impls;

use App\Services\ProductGroupService;
use App\Models\ProductGroup;

class ProductGroupServiceImpl implements ProductGroupService
{
    public function create(
        $code,
        $name
    )
    {


    }

    public function read()
    {
        return ProductGroup::paginate();
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