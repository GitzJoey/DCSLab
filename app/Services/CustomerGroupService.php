<?php

namespace App\Services;

interface CustomerGroupService
{
    public function create(
        $company_id,
        $code,
        $name,
        $is_member_card,
        $max_open_invoice,
        $max_outstanding_invoice,
        $max_invoice_age,
        $payment_term,
        $selling_point,
        $selling_point_multiple,
        $sell_at_cost,
        $global_markup_percent,
        $global_markup_nominal,
        $global_discount_percent,
        $global_discount_nominal,
        $is_rounding,
        $round_on,
        $round_digit,
        $remarks,
        $cash_id
    );

    public function read();

    public function getAllCustomerGroup();

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $is_member_card,
        $max_open_invoice,
        $max_outstanding_invoice,
        $max_invoice_age,
        $payment_term,
        $selling_point,
        $selling_point_multiple,
        $sell_at_cost,
        $global_markup_percent,
        $global_markup_nominal,
        $global_discount_percent,
        $global_discount_nominal,
        $is_rounding,
        $round_on,
        $round_digit,
        $remarks,
        $cash_id
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}