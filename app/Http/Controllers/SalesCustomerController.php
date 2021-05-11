<?php

namespace App\Http\Controllers;

use App\Services\SalesCustomerService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class SalesCustomerController extends Controller
{
    private $salesCustomerService;

    public function __construct(SalesCustomerService $salesCustomerService)
    {
        $this->middleware('auth');
        $this->salesCustomerService = $salesCustomerService;
    }

    public function index()
    {
        $test = $this->read();

        return view('sales.customer.customers.index', compact('test'));
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
            'sales_customer_group_id' => 'required|max:255',
            'sales_territory' => 'required|max:255',
            'use_limit_outstanding_notes' => 'required',
            'limit_outstanding_notes' => 'required|max:255',
            'use_limit_payable_nominal' => 'required',
            'limit_payable_nominal' => 'required|max:255',
            'use_limit_due_date' => 'required',
            'limit_due_date' => 'required|max:255',
            'term' => 'required|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:255',
            'contact' => 'required|max:255',
            'tax_id' => 'required|max:255',
            'remarks' => 'required|max:255',
            'is_active' => 'required',
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->salesCustomerService->create(
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
            $request['is_active'],
            $rolePermissions
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
        $inputtedRolePermissions = [];
        for ($i = 0; $i < count($request['permissions']); $i++) {
            array_push($inputtedRolePermissions, array(
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

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
            $request['is_active'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->salesCustomerService->delete($id);

        return response()->json();
    }
}