<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\WarehouseService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;
class WarehouseController extends Controller
{
    private $warehouseService;
    private $activityLogService;

    public function __construct(WarehouseService $warehouseService, ActivityLogService $activityLogService)
    {
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
        return $this->warehouseService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'warehouses'),
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
            $request['status']);

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

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }    }

    public function delete($id)
    {
        $result = $this->warehouseService->delete($id);

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