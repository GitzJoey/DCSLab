<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\BranchService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Rules\uniqueCode;
use Vinkla\Hashids\Facades\Hashids;
use App\Actions\RandomGenerator;

class BranchController extends BaseController
{
    private $branchService;
    private $activityLogService;

    public function __construct(BranchService $branchService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->branchService = $branchService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.branches.index');
    }

    public function read()
    {
        if (!parent::hasSelectedCompanyOrCompany())
            return response()->error(trans('error_messages.unable_to_find_selected_company'));

        $userId = Auth::user()->id;
        return $this->branchService->read($userId);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'branches')],
            'name' => 'required|min:3|max:255',
            'status' => 'required'
        ]);

        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateOne(99999999);
        };

        $result = $this->branchService->create(
            Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['remarks'],
            $request['status']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'company_id' => 'required|max:255' ,
            'code' => new uniqueCode('update', $id, 'branches'),
            'name' => 'required|min:3|max:255',
            'status' => 'required'
        ]);

        $result = $this->branchService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['remarks'],
            $request['status'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->branchService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}