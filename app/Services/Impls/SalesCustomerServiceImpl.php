<?php

namespace App\Services\Impls;

use Exception;
use App\Services\SalesCustomerService;
use App\Models\SalesCustomer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

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
        $use_limit_age_notes,
        $limit_age_notes,
        $term,
        $address,
        $city,
        $contact,
        $tax_id,
        $remarks,
        $status
    )
    {

        DB::beginTransaction();

        try {
            $customer = new SalesCustomer();
            $customer->code = $code;
            $customer->name = $name;
            $customer->sales_customer_group_id = $sales_customer_group_id;
            $customer->sales_territory = $sales_territory;
            $customer->use_limit_outstanding_notes = $use_limit_outstanding_notes;
            $customer->limit_outstanding_notes = $limit_outstanding_notes;
            $customer->use_limit_payable_nominal = $use_limit_payable_nominal;
            $customer->use_limit_age_notes = $use_limit_age_notes;
            $customer->limit_age_notes = $limit_age_notes;
            $customer->term = $term;
            $customer->address = $address;
            $customer->city = $city;
            $customer->contact = $contact;
            $customer->tax_id = $tax_id;
            $customer->remarks = $remarks;
            $customer->status = $status;

            $customer->save();

            DB::commit();

            return $customer->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return SalesCustomer::with('sales_customer_group')->paginate();
    }


    public function update(
        $id,
        $code,
        $name,
        $sales_customer_group_id,
        $sales_territory,
        $use_limit_outstanding_notes,
        $limit_outstanding_notes,
        $use_limit_payable_nominal,
        $limit_payable_nominal,
        $use_limit_age_notes,
        $limit_age_notes,
        $term,
        $address,
        $city,
        $contact,
        $tax_id,
        $remarks,
        $status
    )
    {
        DB::beginTransaction();

        try {
            $customer = SalesCustomer::where('id', '=', $id);

            $retval = $customer->update([
                'code' => $code,
                'name' => $name,
                'sales_customer_group_id' => $sales_customer_group_id,
                'sales_territory' => $sales_territory,
                'use_limit_outstanding_notes' => $use_limit_outstanding_notes,
                'limit_outstanding_notes' => $limit_outstanding_notes,
                'use_limit_payable_nominal' => $use_limit_payable_nominal,
                'limit_payable_nominal' => $limit_payable_nominal,
                'use_limit_age_notes' =>  $use_limit_age_notes,
                'limit_age_notes' => $limit_age_notes,
                'term' => $term,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'tax_id' => $tax_id,
                'remarks' => $remarks,
                'status' => $status
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
        $salescustomer = SalesCustomer::find($id);

        return $salescustomer->delete();
        
    }
}