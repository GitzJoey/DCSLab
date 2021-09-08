<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\UnitService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class UnitController extends Controller
{
    private $UnitService;
    private $activityLogService;

    public function __construct(UnitService $UnitService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->UnitService = $UnitService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('product.units.index');
    }

    public function read()
    {
        return $this->UnitService->read();
    }

    public function getAllActiveUnit()
    {
        return $this->UnitService->getAllActiveUnit();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'units'),
            'name' => 'required|max:255'
        ]);

        $result = $this->UnitService->create($request['code'], $request['name']);

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
            'code' => new uniqueCode('update', $id, 'units'),
            'name' => 'required|max:255',
        ]);

        $result = $this->UnitService->update(
            $id,
            $request['code'],
            $request['name'],
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
        $result = $this->UnitService->delete($id);

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