<?php

namespace App\Services\Impls;

use App\Services\BranchService;
use App\Models\Branch;

class BranchServiceImpl implements BranchService
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
        return Branch::with('company')->paginate();
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