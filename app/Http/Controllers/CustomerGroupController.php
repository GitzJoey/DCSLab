<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\CustomerGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;
use App\Models\CustomerGroup;

class CustomerGroupController extends BaseController
{
    private $CustomerGroupService;
    private $activityLogService;

    public function __construct(CustomerGroupService $CustomerGroupService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->CustomerGroupService = $CustomerGroupService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('sales.customer.groups.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
            return response()->error(trans('error_messages.unable_to_find_selected_company'));
            
        $userId = Auth::user()->id;
        return $this->CustomerGroupService->read($userId);
    }

    public function getAllCustomerGroup()
    {
        return $this->CustomerGroupService->getAllCustomerGroup();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'customergroups')],
            'name' => 'required|min:3|max:255|alpha',
        ]);

        $randomGenerator = new randomGenerator();
        $code = $request['code'];
        if ($code == 'AUTO') {
            $code_count = 1;
            do {
                $code = $randomGenerator->generateOne(99999999);
                $code_count = CustomerGroup::where('code', $code)->count();
            }
            while ($code_count != 0);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $is_member_card = $request['is_member_card'] == 'on' ? 1 : $request['is_member_card'];
        $is_member_card = is_null($is_member_card) ? 0 : $is_member_card;
        $is_member_card = is_numeric($is_member_card) ? $is_member_card : 0;
        
        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'] == 'on' ? 1 : $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes = is_null($use_limit_outstanding_notes) ? 0 : $use_limit_outstanding_notes;
        $use_limit_outstanding_notes = is_numeric($use_limit_outstanding_notes) ? $use_limit_outstanding_notes : 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'] == 'on' ? 1 : $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal = is_null($use_limit_payable_nominal) ? 0 : $use_limit_payable_nominal;
        $use_limit_payable_nominal = is_numeric($use_limit_payable_nominal) ? $use_limit_payable_nominal : 0;

        $use_limit_age_notes = $request['use_limit_age_notes'] == 'on' ? 1 : $request['use_limit_age_notes'];
        $use_limit_age_notes = is_null($use_limit_age_notes) ? 0 : $use_limit_age_notes;
        $use_limit_age_notes = is_numeric($use_limit_age_notes) ? $use_limit_age_notes : 0;

        $sell_at_capital_price = $request['sell_at_capital_price'] == 'on' ? 1 : $request['sell_at_capital_price'];
        $sell_at_capital_price = is_null($sell_at_capital_price) ? 0 : $sell_at_capital_price;
        $sell_at_capital_price = is_numeric($sell_at_capital_price) ? $sell_at_capital_price : 0;

        $is_rounding = $request['is_rounding'] == 'on' ? 1 : $request['is_rounding'];
        $is_rounding = is_null($is_rounding) ? 0 : $is_rounding;
        $is_rounding = is_numeric($is_rounding) ? $is_rounding : 0;

        $result = $this->CustomerGroupService->create(
            $company_id,
            $code,
            $request['name'],
            $is_member_card,
            $use_limit_outstanding_notes,
            $request['limit_outstanding_notes'],
            $use_limit_payable_nominal,
            $request['limit_payable_nominal'],
            $use_limit_age_notes,
            $request['limit_age_notes'],
            $request['term'],
            $request['selling_point'],
            $request['selling_point_multiple'],
            $sell_at_capital_price,
            $request['global_markup_percent'],
            $request['global_markup_nominal'],
            $request['global_discount_percent'],
            $request['global_discount_nominal'],
            $is_rounding,
            $request['round_on'],
            $request['round_digit'],
            $request['remarks'],
            Hashids::decode($request['cash_id'])[0],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' =>  new uniqueCode('update', $id, 'customergroups'),
            'name' => 'required|min:3|max:255|alpha',
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $is_member_card = $request['is_member_card'] == 'on' ? 1 : $request['is_member_card'];
        $is_member_card = is_null($is_member_card) ? 0 : $is_member_card;
        $is_member_card = is_numeric($is_member_card) ? $is_member_card : 0;
        
        $use_limit_outstanding_notes = $request['use_limit_outstanding_notes'] == 'on' ? 1 : $request['use_limit_outstanding_notes'];
        $use_limit_outstanding_notes = is_null($use_limit_outstanding_notes) ? 0 : $use_limit_outstanding_notes;
        $use_limit_outstanding_notes = is_numeric($use_limit_outstanding_notes) ? $use_limit_outstanding_notes : 0;

        $use_limit_payable_nominal = $request['use_limit_payable_nominal'] == 'on' ? 1 : $request['use_limit_payable_nominal'];
        $use_limit_payable_nominal = is_null($use_limit_payable_nominal) ? 0 : $use_limit_payable_nominal;
        $use_limit_payable_nominal = is_numeric($use_limit_payable_nominal) ? $use_limit_payable_nominal : 0;

        $use_limit_age_notes = $request['use_limit_age_notes'] == 'on' ? 1 : $request['use_limit_age_notes'];
        $use_limit_age_notes = is_null($use_limit_age_notes) ? 0 : $use_limit_age_notes;
        $use_limit_age_notes = is_numeric($use_limit_age_notes) ? $use_limit_age_notes : 0;

        $sell_at_capital_price = $request['sell_at_capital_price'] == 'on' ? 1 : $request['sell_at_capital_price'];
        $sell_at_capital_price = is_null($sell_at_capital_price) ? 0 : $sell_at_capital_price;
        $sell_at_capital_price = is_numeric($sell_at_capital_price) ? $sell_at_capital_price : 0;

        $is_rounding = $request['is_rounding'] == 'on' ? 1 : $request['is_rounding'];
        $is_rounding = is_null($is_rounding) ? 0 : $is_rounding;
        $is_rounding = is_numeric($is_rounding) ? $is_rounding : 0;

        $result = $this->CustomerGroupService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
            $is_member_card,
            $use_limit_outstanding_notes,
            $request['limit_outstanding_notes'],
            $use_limit_payable_nominal,
            $request['limit_payable_nominal'],
            $use_limit_age_notes,
            $request['limit_age_notes'],
            $request['term'],
            $request['selling_point'],
            $request['selling_point_multiple'],
            $sell_at_capital_price,
            $request['global_markup_percent'],
            $request['global_markup_nominal'],
            $request['global_discount_percent'],
            $request['global_discount_nominal'],
            $is_rounding,
            $request['round_on'],
            $request['round_digit'],
            $request['remarks'],
            Hashids::decode($request['cash_id'])[0], 
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->CustomerGroupService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}