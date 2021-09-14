<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\CustomerService;

use Vinkla\Hashids\Facades\Hashids;
use App\Services\ActivityLogService;

class CustomerController extends Controller
{
    private $CustomerService;
    private $activityLogService;

    public function __construct(CustomerService $CustomerService, ActivityLogService $activityLogService)
    {
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
        return $this->CustomerService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'customers'),
            'name' => 'required|max:255',
            'status' => 'required',
        ]);

        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes == 'on' ? $use_limit_outstanding_notes = 1 : $use_limit_outstanding_notes = 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal == 'on' ? $use_limit_payable_nominal = 1 : $use_limit_payable_nominal = 0;

        $use_limit_age_notes = $request['use_limit_age_notes'];
        $use_limit_age_notes == 'on' ? $use_limit_age_notes = 1 : $use_limit_age_notes = 0;

        $result = $this->CustomerService->create(
            $request['code'],
            $request['name'],
            Hashids::decode($request['customer_group_id'])[0], 
            $request['sales_territory'],
            $use_limit_outstanding_notes,
            $request['limit_outstanding_notes'],
            $use_limit_payable_nominal,
            $request['limit_payable_nominal'],
            $use_limit_age_notes,
            $request['limit_age_notes'],
            $request['term'],
            $request['address'],
            $request['city'],
            $request['contact'],
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
            'name' => 'required|max:255',
            'status' => 'required',
        ]);

        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes == 'on' ? $use_limit_outstanding_notes = 1 : $use_limit_outstanding_notes = 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal == 'on' ? $use_limit_payable_nominal = 1 : $use_limit_payable_nominal = 0;

        $use_limit_age_notes = $request['use_limit_age_notes'];
        $use_limit_age_notes == 'on' ? $use_limit_age_notes = 1 : $use_limit_age_notes = 0;

        $result = $this->CustomerService->update(
            $id,
            $request['code'],
            $request['name'],
            Hashids::decode($request['customer_group_id'])[0], 
            $request['sales_territory'],
            $use_limit_outstanding_notes,
            $request['limit_outstanding_notes'],
            $use_limit_payable_nominal,
            $request['limit_payable_nominal'],
            $use_limit_age_notes,
            $request['limit_age_notes'],
            $request['term'],
            $request['address'],
            $request['city'],
            $request['contact'],
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