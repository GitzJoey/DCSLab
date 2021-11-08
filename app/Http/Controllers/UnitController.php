<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class UnitController extends BaseController
{
    private $UnitService;
    private $activityLogService;

    public function __construct(UnitService $UnitService, ActivityLogService $activityLogService)
    {
        parent::__construct();

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

    public function getAllUnit()
    {
        return $this->UnitService->getAllUnit();
    }

    public function getAllProduct()
    {
        return $this->UnitService->getAllProduct();
    }

    public function getAllService()
    {
        return $this->UnitService->getAllService();
    }

    public function GetAllProductandService()
    {
        return $this->UnitService->GetAllProductandService();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'units')],
            'name' => 'required|max:255'
        ]);

        $result = $this->UnitService->create(
            // Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
            $request['category']
        );
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
            // Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
            $request['category']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->UnitService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}