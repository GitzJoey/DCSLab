<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\SalesCustomerGroupService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class SalesCustomerGroupController extends Controller
{
    private $salesCustomerGroupService;
    private $activityLogService;

    public function __construct(SalesCustomerGroupService $salesCustomerGroupService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->salesCustomerGroupService = $salesCustomerGroupService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

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
            'limit_outstanding_notes' => 'required|max:255',
            'limit_payable_nominal' => 'required|max:255',
            'limit_age_notes' => 'required|max:255',
            'term' => 'required|max:255',
            'selling_point' => 'required|max:255',
            'selling_point_multiple' => 'required|max:255',
            'sell_at_capital_price' => 'required|max:255',
            'global_markup_percent' => 'required|max:255',
            'global_markup_nominal' => 'required|max:255',
            'global_discount_percent' => 'required|max:255',
            'global_discount_nominal' => 'required|max:255',
            'round_digit' => 'required|max:255',
            'remarks' => 'required|max:255',
        ]);

        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes == 'on' ? $use_limit_outstanding_notes = 1 : $use_limit_outstanding_notes = 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal == 'on' ? $use_limit_payable_nominal = 1 : $use_limit_payable_nominal = 0;

        $use_limit_age_notes = $request['use_limit_age_notes'];
        $use_limit_age_notes == 'on' ? $use_limit_age_notes = 1 : $use_limit_age_notes = 0;

        $is_rounding = $request['is_rounding'];
        $is_rounding == 'on' ? $is_rounding = 1 : $is_rounding = 0;

        $result = $this->salesCustomerGroupService->create(
            $request['code'],
            $request['name'],
            $request['is_member_card'],
            $use_limit_outstanding_notes,
            $request['limit_outstanding_notes'],
            $use_limit_payable_nominal,
            $request['limit_payable_nominal'],
            $use_limit_age_notes,
            $request['limit_age_notes'],
            $request['term'],
            $request['selling_point'],
            $request['selling_point_multiple'],
            $request['sell_at_capital_price'],
            $request['global_markup_percent'],
            $request['global_markup_nominal'],
            $request['global_discount_percent'],
            $request['global_discount_nominal'],
            $is_rounding,
            $request['round_on'],
            $request['round_digit'],
            $request['remarks'],
            $request['finance_cash_id'],
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
            'limit_outstanding_notes' => 'required|max:255',
            'limit_payable_nominal' => 'required|max:255',
            'limit_age_notes' => 'required|max:255',
            'term' => 'required|max:255',
            'selling_point' => 'required|max:255',
            'selling_point_multiple' => 'required|max:255',
            'sell_at_capital_price' => 'required|max:255',
            'global_markup_percent' => 'required|max:255',
            'global_markup_nominal' => 'required|max:255',
            'global_discount_percent' => 'required|max:255',
            'global_discount_nominal' => 'required|max:255',
            'round_digit' => 'required|max:255',
            'remarks' => 'required|max:255',
        ]);

        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes == 'on' ? $use_limit_outstanding_notes = 1 : $use_limit_outstanding_notes = 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal == 'on' ? $use_limit_payable_nominal = 1 : $use_limit_payable_nominal = 0;

        $use_limit_age_notes = $request['use_limit_age_notes'];
        $use_limit_age_notes == 'on' ? $use_limit_age_notes = 1 : $use_limit_age_notes = 0;

        $is_rounding = $request['is_rounding'];
        $is_rounding == 'on' ? $is_rounding = 1 : $is_rounding = 0;

        $result = $this->salesCustomerGroupService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['is_member_card'],
            $use_limit_outstanding_notes,
            $request['limit_outstanding_notes'],
            $use_limit_payable_nominal,
            $request['limit_payable_nominal'],
            $use_limit_age_notes,
            $request['limit_age_notes'],
            $request['term'],
            $request['selling_point'],
            $request['selling_point_multiple'],
            $request['sell_at_capital_price'],
            $request['global_markup_percent'],
            $request['global_markup_nominal'],
            $request['global_discount_percent'],
            $request['global_discount_nominal'],
            $is_rounding,
            $request['round_on'],
            $request['round_digit'],
            $request['remarks'],
            $request['finance_cash_id'],
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

    public function delete($id)
    {
        $result = $this->salesCustomerGroupService->delete($id);

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