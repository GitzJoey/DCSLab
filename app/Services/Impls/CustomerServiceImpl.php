<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Services\CustomerService;

class CustomerServiceImpl implements CustomerService
{
    public function create(
        $company_id,
        $code,
        $name,
        $is_member,
        $customer_group_id,
        $zone,
        $max_open_invoice,
        $max_outstanding_invoice,
        $max_invoice_age,
        $payment_term,
        $tax_id,
        $remarks,
        $status,
        $customer_addresses
    )
    {
        DB::beginTransaction();

        try {
            $customer = new Customer();
            $customer->company_id = $company_id;
            $customer->code = $code;
            $customer->name = $name;
            $customer->is_member = $is_member;
            $customer->customer_group_id = $customer_group_id;
            $customer->zone = $zone;
            $customer->max_open_invoice = $max_open_invoice;
            $customer->max_outstanding_invoice = $max_outstanding_invoice;
            $customer->max_invoice_age = $max_invoice_age;
            $customer->payment_term = $payment_term;
            $customer->tax_id = $tax_id;
            $customer->remarks = $remarks;
            $customer->status = $status;

            $customer->save();

            if (empty($customer_addresses) === false) {
                $ca = [];
                foreach ($customer_addresses as $customer_address) {
                    array_push($ca, new CustomerAddress(array(
                        'company_id' => $company_id,
                        'customer_id' => $customer['id'],
                        'address' => $customer_address['address'],
                        'city' => $customer_address['city'],
                        'contact' => $customer_address['contact'],
                        'remarks' => $customer_address['address_remarks'],
                    )));
                }
                $customer->customerAddress()->saveMany($ca);
            }

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
        return Customer::with('customerGroup', 'customerAddress')->bySelectedCompany()->paginate();
    }

    public function update(
        $id,
        $company_id,
        $code,
        $name,
        $is_member,
        $customer_group_id,
        $zone,
        $max_open_invoice,
        $max_outstanding_invoice,
        $max_invoice_age,
        $payment_term,
        $tax_id,
        $remarks,
        $status,
        $customer_addresses
    )
    {
        DB::beginTransaction();

        try {
            $customer = Customer::where('id', '=', $id);

            $retval = $customer->update([
                'company_id' => $company_id,
                'code' => $code,
                'name' => $name,
                'is_member' => $is_member,
                'customer_group_id' => $customer_group_id,
                'zone' => $zone,
                'max_open_invoice' => $max_open_invoice,
                'max_outstanding_invoice' => $max_outstanding_invoice,
                'max_invoice_age' => $max_invoice_age,
                'payment_term' => $payment_term,
                'tax_id' => $tax_id,
                'remarks' => $remarks,
                'status' => $status
            ]);

            
            $ca = [];
            foreach ($customer_addresses as $customer_address) {
                array_push($ca, array(
                    'id' => $customer_address['id'],
                    'company_id' => $company_id,
                    'customer_id' => $id,
                    'address' => $customer_address['address'],
                    'city' => $customer_address['city'],
                    'contact' => $customer_address['contact'],
                    'remarks' => $customer_address['address_remarks'],
                ));
            }

            $caIds = [];
            foreach ($ca as $caId)
            {
                array_push($caIds, $caId['id']);
            }

            $caOld = Customer::find($id);
            $caIdsOld = $caOld->customerAddress()->pluck('id')->ToArray();

            $deletedCustomerAddressIds = [];
            $deletedCustomerAddressIds = array_diff($caIdsOld, $caIds);

            foreach ($deletedCustomerAddressIds as $deletedCustomerAddressId) {
                $customerAddress = CustomerAddress::find($deletedCustomerAddressId);
                $retval = $customerAddress->delete();
            }

            if (empty($ca) === false) {
                CustomerAddress::upsert(
                    $ca,
                    [
                        'id'
                    ], 
                    [
                        'address',
                        'city',
                        'contact',
                        'remarks'
                    ]
                );
            }  

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
        $customer = Customer::find($id);

        return $customer->delete();
    }

    public function checkDuplicatedCode($crud_status, $id, $code)
    {
        switch($crud_status) {
            case 'create': 
                $count = Customer::where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
            case 'update':
                $count = Customer::where('id', '<>' , $id)
                ->where('code', '=', $code)
                ->whereNull('deleted_at')
                ->count();
                return $count;
        }
    }
}