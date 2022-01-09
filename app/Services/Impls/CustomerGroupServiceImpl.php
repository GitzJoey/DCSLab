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
    )
    {
        DB::beginTransaction();

        try {
            $customergroup = new CustomerGroup();
            $customergroup->company_id = $company_id;
            $customergroup->code = $code;
            $customergroup->name = $name;
            $customergroup->is_member_card = $is_member_card;
            $customergroup->max_open_invoice = $max_open_invoice;
            $customergroup->max_outstanding_invoice = $max_outstanding_invoice;
            $customergroup->max_invoice_age = $max_invoice_age;
            $customergroup->payment_term = $payment_term;
            $customergroup->selling_point = $selling_point;
            $customergroup->selling_point_multiple = $selling_point_multiple;
            $customergroup->sell_at_cost = $sell_at_cost;
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
        return CustomerGroup::with('cash', 'company')->bySelectedCompany()->paginate();
    }

    public function getAllCustomerGroup()
    {
        return CustomerGroup::all();
    }

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

    )
    {
        DB::beginTransaction();

        try {
            $customergroup = CustomerGroup::where('id', '=', $id);

            $retval = $customergroup->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'is_member_card' => $is_member_card,
                'max_open_invoice' => $max_open_invoice,
                'max_outstanding_invoice' => $max_outstanding_invoice,
                'max_invoice_age' => $max_invoice_age,
                'payment_term' => $payment_term,
                'selling_point' => $selling_point,
                'selling_point_multiple' => $selling_point_multiple,
                'sell_at_cost' => $sell_at_cost,
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