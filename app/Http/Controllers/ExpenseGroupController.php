<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ExpenseGroupService;
use Illuminate\Http\Request;

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
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'expensegroups')],
            'name' => 'required|max:255',
        ]);

        $result = $this->expenseGroupService->create(
            Hashids::decode($request['company_id'])[0], 
            $request['code'],
            $request['name'], 
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'code' => new uniqueCode('update', $id, 'expensegroups'),
            'name' => 'required|max:255',
        ]);

        $result = $this->expenseGroupService->update(
            $id,
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