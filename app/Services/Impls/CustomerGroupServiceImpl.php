<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CustomerGroupService;
use App\Models\CustomerGroup;

class CustomerGroupServiceImpl implements CustomerGroupService
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
        $cash_id
    )
    {
        DB::beginTransaction();

        try {
            $customergroup = new CustomerGroup();
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
            $customergroup->cash_id = $cash_id;

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
        return CustomerGroup::with('cash')->paginate();
    }

    public function getAllCustomerGroup()
    {
        return CustomerGroup::all();
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
        $cash_id

    )
    {
        DB::beginTransaction();

        try {
            $customergroup = CustomerGroup::where('id', '=', $id);

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
                'cash_id' => $cash_id
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
        $customergroup = CustomerGroup::find($id);

        return $customergroup->delete();
        
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = CustomerGroup::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = CustomerGroup::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}