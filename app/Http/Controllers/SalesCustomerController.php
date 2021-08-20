<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\SalesCustomerService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class SalesCustomerController extends Controller
{
    private $salesCustomerService;
    private $activityLogService;

    public function __construct(SalesCustomerService $salesCustomerService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->salesCustomerService = $salesCustomerService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('sales.customer.customers.index');
    }

    public function read()
    {
        return $this->salesCustomerService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'sales_territory' => 'required|max:255',
            'limit_outstanding_notes' => 'required|max:255',
            'limit_payable_nominal' => 'required|max:255',
            'limit_due_date' => 'required|max:255',
            'term' => 'required|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:255',
            'contact' => 'required|max:255',
            'tax_id' => 'required|max:255',
            'remarks' => 'required|max:255',
            'status' => 'required',
        ]);

        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes == 'on' ? $use_limit_outstanding_notes = 1 : $use_limit_outstanding_notes = 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal == 'on' ? $use_limit_payable_nominal = 1 : $use_limit_payable_nominal = 0;

        $use_limit_due_date = $request['use_limit_due_date'];
        $use_limit_due_date == 'on' ? $use_limit_due_date = 1 : $use_limit_due_date = 0;

        $result = $this->salesCustomerService->update(
            $request['code'],
            $request['name'],
            $request['sales_customer_group_id'],
            $request['sales_territory'],
            $request['use_limit_outstanding_notes'],
            $request['limit_outstanding_notes'],
            $request['use_limit_payable_nominal'],
            $request['limit_payable_nominal'],
            $request['use_limit_due_date'],
            $request['limit_due_date'],
            $request['term'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['tax_id'],
            $request['remarks'],
            $request['status'],
        );

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'sales_territory' => 'required|max:255',
            'limit_outstanding_notes' => 'required|max:255',
            'limit_payable_nominal' => 'required|max:255',
            'limit_due_date' => 'required|max:255',
            'term' => 'required|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:255',
            'contact' => 'required|max:255',
            'tax_id' => 'required|max:255',
            'remarks' => 'required|max:255',
            'status' => 'required',
        ]);

        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes == 'on' ? $use_limit_outstanding_notes = 1 : $use_limit_outstanding_notes = 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal == 'on' ? $use_limit_payable_nominal = 1 : $use_limit_payable_nominal = 0;

        $use_limit_due_date = $request['use_limit_due_date'];
        $use_limit_due_date == 'on' ? $use_limit_due_date = 1 : $use_limit_due_date = 0;

        $result = $this->salesCustomerService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['sales_customer_group_id'],
            $request['sales_territory'],
            $request['use_limit_outstanding_notes'],
            $request['limit_outstanding_notes'],
            $request['use_limit_payable_nominal'],
            $request['limit_payable_nominal'],
            $request['use_limit_due_date'],
            $request['limit_due_date'],
            $request['term'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['tax_id'],
            $request['remarks'],
            $request['status'],
        );

        return response()->json();
    }

    public function delete($id)
    {
        $result = $this->salesCustomerService->delete($id);

        if ($result == false) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }
}