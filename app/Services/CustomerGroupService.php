<?php

namespace App\Services;

interface CustomerGroupService
{
    public function create(
        $company_id,
        $code,
        $name,
        $is_member_card,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_age_notes,
        $limit_age_notes,
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
        $cash_id
    );

    public function read($userId);

    public function getAllCustomerGroup();

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $is_member_card,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_age_notes,
        $limit_age_notes,
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
        $cash_id
    );

    public function delete($id);

    public function checkDuplicatedCode($crud_status, $id, $code);
}