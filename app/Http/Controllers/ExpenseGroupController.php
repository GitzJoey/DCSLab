<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ExpenseGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;
use Vinkla\Hashids\Facades\Hashids;

class ExpenseGroupController extends BaseController
{
    private $expenseGroupService;
    private $activityLogService;

    public function __construct(ExpenseGroupService $expenseGroupService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->expenseGroupService = $expenseGroupService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('finance.expensegroups.index');
    }

    public function read()
    {
        return $this->expenseGroupService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'expensegroups')],
            'name' => 'required|min:3|max:255',
        ]);

        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateOne(99999999);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->expenseGroupService->create(
            $company_id,
            $request['code'],
            $request['name'], 
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'expensegroups'),
            'name' => 'required|min:3|max:255|alpha_dash|alpha_num',
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->expenseGroupService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->expenseGroupService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}