<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\CashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class CashController extends BaseController
{
    private $CashService;
    private $activityLogService;

    public function __construct(CashService $CashService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->CashService = $CashService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('finance.cashes.index');
    }

    public function read()
    {
        $userId = Auth::user()->id;
        return $this->CashService->read($userId);
    }

    public function getAllActiveCash()
    {
        return $this->CashService->getAllActiveCash();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'cashes')],
            'name' => 'required|max:255',
            'status' => 'required'
        ]);
        
        $is_bank = $request['is_bank'];
        $is_bank == 'on' ? $is_bank = 1 : $is_bank = 0;

        $result = $this->CashService->create(
            Hashids::decode($request['company_id'])[0],
            $request['code'], 
            $request['name'], 
            $is_bank, 
            $request['status']
        );
        
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'cashes'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $is_bank = $request['is_bank'];
        $is_bank == 'on' ? $is_bank = 1 : $is_bank = 0;

        $result = $this->CashService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
            $is_bank,
            $request['status'],
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->CashService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
