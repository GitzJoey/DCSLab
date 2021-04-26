<?php

namespace App\Services\Impls;

use App\Services\FinanceCashService;
use App\Models\FinanceCash;

class FinanceCashServiceImpl implements FinanceCashService
{
    public function create(
        $code,
        $name,
        $is_bank,
        $is_active
    )
    {


    }

    public function read()
    {
        return FinanceCash::get();
    }


    public function update(
        $code,
        $name,
        $is_bank,
        $is_active
    )
    {

        
    }


    public function delete($id)
    {

        
    }
}