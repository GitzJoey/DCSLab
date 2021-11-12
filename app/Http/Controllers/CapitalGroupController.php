<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\CapitalGroupService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;

class CapitalGroupController extends BaseController
{
    private $capitalGroupService;
    private $activityLogService;

    public function __construct(CapitalGroupService $capitalGroupService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->capitalGroupService = $capitalGroupService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('finance.capitalgroups.index');
    }

    public function read()
    {
        $userId = Auth::user()->id;
        return $this->capitalGroupService->read($userId);
    }

    public function getAllActiveCapitalGroup()
    {
        return $this->capitalGroupService->getAllActiveCapitalGroup();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'capitalgroups')],
            'name' => 'required|max:255'
        ]);

        $result = $this->capitalGroupService->create(
            Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name']
        );
        
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'capitalgroups'),
            'name' => 'required|max:255',
        ]);

        $result = $this->capitalGroupService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->capitalGroupService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
