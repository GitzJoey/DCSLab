<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Vinkla\Hashids\Facades\Hashids;

class WarehouseController extends BaseController
{
    private $warehouseService;
    private $activityLogService;

    public function __construct(WarehouseService $warehouseService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->warehouseService = $warehouseService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.warehouses.index');
    }

    public function read()
    {
        $userId = Auth::user()->id;
        return $this->warehouseService->read($userId);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'warehouses')],
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->warehouseService->create(
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
            'company_id' => 'required',
            'code' => new uniqueCode('update', $id, 'warehouses'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->warehouseService->update(
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
        $result = $this->warehouseService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}