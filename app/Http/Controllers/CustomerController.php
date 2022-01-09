<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;
use App\Models\Customer;

class CustomerController extends BaseController
{
    private $CustomerService;
    private $activityLogService;

    public function __construct(CustomerService $CustomerService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->CustomerService = $CustomerService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('sales.customer.customers.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
            return response()->error(trans('error_messages.unable_to_find_selected_company'));
            
        return $this->CustomerService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'customers')],
            'name' => 'required|min:3|max:255',
            'status' => 'required',
        ]);

        $randomGenerator = new randomGenerator();
        $code = $request['code'];
        if ($code == 'AUTO') {
            $code_count = 1;
            do {
                $code = $randomGenerator->generateOne(99999999);
                $code_count = Customer::where('code', $code)->count();
            }
            while ($code_count != 0);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $customer_addresses = [];
        $count_address = count($request['address']);
        for ($i = 0; $i < $count_address; $i++) {
            array_push($customer_addresses, array (
                'company_id' => $company_id,
                'customer_id' => null,
                'address' => $request['address'][$i],
                'city' => $request['city'][$i],
                'contact' => $request['contact'][$i],
                'address_remarks' => $request['address_remarks'][$i]
            ));
        }

        $result = $this->CustomerService->create(
            $company_id,
            $code,
            $request['name'],
            Hashids::decode($request['customer_group_id'])[0], 
            $request['sales_territory'],
            $request['max_open_invoice'],
            $request['max_outstanding_invoice'],
            $request['max_invoice_age'],
            $request['payment_term'],
            $customer_addresses,
            $request['tax_id'],
            $request['remarks'],
            $request['status'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' =>  new uniqueCode('update', $id, 'customers'),
            'name' => 'required|min:3|max:255',
            'status' => 'required',
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $customer_addresses = [];
        array_push($customer_addresses, array (
            'company_id' => $company_id,
            'customer_id' => null,
            'address' => $request['address'],
            'city' => $request['city'],
            'contact' => $request['contact'],
            'remarks' => ''
        ));
        
        $customer_addresses = [];
        if (empty($request['customer_address_hId']) === false) {
            $count_address = count($request['address']);

            for ($i = 0; $i < $count_address; $i++) {
                $customer_address_id = $request['customer_address_hId'][$i] != null ? Hashids::decode($request['customer_address_hId'][$i])[0] : null;
                
                array_push($customer_addresses, array (
                    'id' => $customer_address_id,
                    'company_id' => $company_id,
                    'customer_id' => null,
                    'address' => $request['address'][$i],
                    'city' => $request['city'][$i],
                    'contact' => $request['contact'][$i],
                    'address_remarks' => $request['address_remarks'][$i]
                ));
            }
        }

        $result = $this->CustomerService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
            Hashids::decode($request['customer_group_id'])[0], 
            $request['sales_territory'],
            $request['max_open_invoice'],
            $request['max_outstanding_invoice'],
            $request['max_invoice_age'],
            $request['payment_term'],
            $customer_addresses,
            $request['tax_id'],
            $request['remarks'],
            $request['status'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->CustomerService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}