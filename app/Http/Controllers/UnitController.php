<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\UnitService;
use Illuminate\Http\Request;

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

        return $result == 0 ? response()->error():response()->success();
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
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->UnitService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}