<?php

namespace App\Http\Controllers;

use App\Services\SalesCustomerGroupService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class SalesCustomerGroupController extends Controller
{
    private $salesCustomerGroupService;

    public function __construct(SalesCustomerGroupService $salesCustomerGroupService)
    {
        $this->middleware('auth');
        $this->salesCustomerGroupService = $salesCustomerGroupService;
    }

    public function index()
    {

        return view('sales.customer.groups.index');
    }

    public function read()
    {
        return $this->salesCustomerGroupService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'is_member_card' => 'required',
            'use_limit_outstanding_notes' => 'required',
            'limit_outstanding_notes' => 'required|max:255',
            'use_limit_payable_nominal' => 'required|max:255',
            'limit_payable_nominal' => 'required',
            'use_limit_due_date' => 'required',
            'limit_due_date' => 'required|max:255',
            'term' => 'required|max:255',
            'selling_point' => 'required|max:255',
            'selling_point_multiple' => 'required|max:255',
            'sell_at_capital_price' => 'required|max:255',
            'global_markup_percent' => 'required|max:255',
            'global_markup_nominal' => 'required|max:255',
            'global_discount_percent' => 'required|max:255',
            'global_discount_nominal' => 'required|max:255',
            'is_rounding' => 'required',
            'round_on' => 'required|max:255',
            'round_digit' => 'required|max:255',
            'remarks' => 'required|max:255',
            'finance_cash_id' => 'required'
    
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->salesCustomerGroupService->create(
            $request['code'],
            $request['name'],
            $request['is_member_card'],
            $request['use_limit_outstanding_notes'],
            $request['limit_outstanding_notes'],
            $request['use_limit_payable_nominal'],
            $request['limit_payable_nominal'],
            $request['use_limit_due_date'],
            $request['limit_due_date'],
            $request['term'],
            $request['selling_point'],
            $request['selling_point_multiple'],
            $request['sell_at_capital_price'],
            $request['global_markup_percent'],
            $request['global_markup_nominal'],
            $request['global_discount_percent'],
            $request['global_discount_nominal'],
            $request['is_rounding'],
            $request['round_on'],
            $request['round_digit'],
            $request['remarks'],
            $request['finance_cash_id'],
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

        $result = $this->salesCustomerGroupService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['is_member_card'],
            $request['use_limit_outstanding_notes'],
            $request['limit_outstanding_notes'],
            $request['use_limit_payable_nominal'],
            $request['limit_payable_nominal'],
            $request['use_limit_due_date'],
            $request['limit_due_date'],
            $request['term'],
            $request['selling_point'],
            $request['selling_point_multiple'],
            $request['sell_at_capital_price'],
            $request['global_markup_percent'],
            $request['global_markup_nominal'],
            $request['global_discount_percent'],
            $request['global_discount_nominal'],
            $request['is_rounding'],
            $request['round_on'],
            $request['round_digit'],
            $request['remarks'],
            $request['finance_cash_id'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->salesCustomerGroupService->delete($id);

        return response()->json();
    }
}