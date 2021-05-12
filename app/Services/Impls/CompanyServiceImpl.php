<?php

namespace App\Services\Impls;

use App\Services\CompanyService;
use App\Models\Company;

class CompanyServiceImpl implements CompanyService
{
    public function create(
        $code,
        $name,
        $is_active
    )
    {


    }

    public function read()
    {
        return Company::get();
    }


    public function update(
        $code,
        $name,
        $is_active
    )
    {

        
    }


    public function delete($id)
    {

        
    }
}