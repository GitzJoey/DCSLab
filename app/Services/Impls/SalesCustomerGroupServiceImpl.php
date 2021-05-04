<?php

namespace App\Services\Impls;

use App\Services\SalesCustomerGroupService;
use App\Models\SalesCustomerGroup;

class SalesCustomerGroupServiceImpl implements SalesCustomerGroupService
{
    public function create(
        $code,
        $name,
        $is_member_card,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_due_date,
        $limit_due_date,
        $term,
        $selling_point,
        $selling_point_multiple,
        $sell_at_capital_price,
        $global_markup_percent,
        $global_markup_nominal,
        $global_discount_percent,
        $global_discount_nominal,
        $is_rounding,
        $round_on,
        $round_digit,
        $remarks,
        $finance_cash_id
    )
    {


    }

    public function read()
    {
        return SalesCustomerGroup::get();
    }


    public function update(
        $code,
        $name,
        $is_member_card,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_due_date,
        $limit_due_date,
        $term,
        $selling_point,
        $selling_point_multiple,
        $sell_at_capital_price,
        $global_markup_percent,
        $global_markup_nominal,
        $global_discount_percent,
        $global_discount_nominal,
        $is_rounding,
        $round_on,
        $round_digit,
        $remarks,
        $finance_cash_id

    )
    {

        
    }


    public function delete($id)
    {

        
    }
}