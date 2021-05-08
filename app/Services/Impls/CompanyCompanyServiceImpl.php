<?php

namespace App\Services\Impls;

use App\Services\CompanyCompanyService;
use App\Models\CompanyCompany;

class CompanyCompanyServiceImpl implements CompanyCompanyService
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
        return CompanyCompany::get();
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