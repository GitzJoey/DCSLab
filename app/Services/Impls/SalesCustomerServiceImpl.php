<?php

namespace App\Services\Impls;

use App\Services\SalesCustomerService;
use App\Models\SalesCustomer;

class SalesCustomerServiceImpl implements SalesCustomerService
{
    public function create(
        $code,
        $name,
        $sales_customer_group_id,
        $sales_territory,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_due_date,
        $limit_due_date,
        $term,
        $address,
        $city,
        $contact,
        $tax_id,
        $remarks,
        $status
    )
    {


    }

    public function read()
    {
        return SalesCustomer::with('sales_customer_group')->paginate();
    }


    public function update(
        $code,
        $name,
        $sales_customer_group_id,
        $sales_territory,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_due_date,
        $limit_due_date,
        $term,
        $address,
        $city,
        $contact,
        $tax_id,
        $remarks,
        $status
    )
    {

        
    }


    public function delete($id)
    {
        $salescustomer = SalesCustomer::find($id);

        return $salescustomer->delete();
        
    }
}