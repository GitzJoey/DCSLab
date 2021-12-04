<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\IncomeGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Actions\RandomGenerator;
use Vinkla\Hashids\Facades\Hashids;

class IncomeGroupController extends BaseController
{
    private $incomeGroupService;
    private $activityLogService;

    public function __construct(IncomeGroupService $incomeGroupService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->incomeGroupService = $incomeGroupService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('finance.incomegroups.index');
    }

    public function read()
    {
        return $this->incomeGroupService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'incomegroups')],
            'name' => 'required|min:3|max:255|alpha_dash|alpha_num',
        ]);

        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateOne(99999999);
        };

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->incomeGroupService->create(
            $company_id,
            $request['code'],
            $request['name'], 
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'incomegroups'),
            'name' => 'required|min:3|max:255|alpha_dash|alpha_num',
        ]);

        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $result = $this->incomeGroupService->update(
            $id,
            $company_id,
            $request['code'],
            $request['name'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->incomeGroupService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}