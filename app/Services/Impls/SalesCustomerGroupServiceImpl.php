<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $finance_cash_id
    )
    {
        DB::beginTransaction();

        try {
            $customergroup = new SalesCustomerGroup();
            $customergroup->code = $code;
            $customergroup->name = $name;
            $customergroup->is_member_card = $is_member_card;
            $customergroup->use_limit_outstanding_notes = $use_limit_outstanding_notes;
            $customergroup->limit_outstanding_notes = $limit_outstanding_notes;
            $customergroup->use_limit_payable_nominal = $use_limit_payable_nominal;
            $customergroup->use_limit_age_notes = $use_limit_age_notes;
            $customergroup->limit_age_notes = $limit_age_notes;
            $customergroup->term = $term;
            $customergroup->selling_point = $selling_point;
            $customergroup->selling_point_multiple = $selling_point_multiple;
            $customergroup->sell_at_capital_price = $sell_at_capital_price;
            $customergroup->global_markup_percent = $global_markup_percent;
            $customergroup->global_markup_nominal = $global_markup_nominal;
            $customergroup->global_discount_percent = $global_discount_percent;
            $customergroup->global_discount_nominal = $global_discount_nominal;
            $customergroup->is_rounding = $is_rounding;
            $customergroup->round_on = $round_on;
            $customergroup->round_digit = $round_digit;
            $customergroup->remarks = $remarks;
            $customergroup->finance_cash_id = $finance_cash_id;

            $customergroup->save();

            DB::commit();

            return $customergroup->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }

    }

    public function read()
    {
        return SalesCustomerGroup::paginate();
    }


    public function update(
        $id,
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
        $finance_cash_id

    )
    {
        DB::beginTransaction();

        try {
            $customergroup = SalesCustomerGroup::where('id', '=', $id);

            $retval = $customergroup->update([
                'code' => $code,
                'name' => $name,
                'is_member_card' => $is_member_card,
                'use_limit_outstanding_notes' => $use_limit_outstanding_notes,
                'limit_outstanding_notes' => $limit_outstanding_notes,
                'use_limit_payable_nominal' => $use_limit_payable_nominal,
                'limit_payable_nominal' => $limit_payable_nominal,
                'use_limit_age_notes' =>  $use_limit_age_notes,
                'limit_age_notes' => $limit_age_notes,
                'term' => $term,
                'selling_point' => $selling_point,
                'selling_point_multiple' => $selling_point_multiple,
                'sell_at_capital_price' => $sell_at_capital_price,
                'global_markup_percent' => $global_markup_percent,
                'global_markup_nominal' => $global_markup_nominal,
                'global_discount_percent' => $global_discount_percent,
                'global_discount_nominal' => $global_discount_nominal,
                'is_rounding' => $is_rounding,
                'round_on' => $round_on,
                'round_digit' => $round_digit,
                'remarks' => $remarks,
                'finance_cash_id' => $finance_cash_id
            ]);

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }        
    }


    public function delete($id)
    {
        $salescustomergroup = SalesCustomerGroup::find($id);

        return $salescustomergroup->delete();
        
    }
}