<?php

namespace App\Services\Impls;

use App\Services\CompanyBranchService;
use App\Models\CompanyBranch;

class CompanyBranchServiceImpl implements CompanyBranchService
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
        return CompanyBranch::get();
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