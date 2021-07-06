<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\HouseService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class HouseController extends Controller
{
    private $houseService;
    private $activityLogService;

    public function __construct(HouseService $houseService, ActivityLogService $activityLogService)
    {
        $this->middleware(middleware:'auth');
        $this->houseService = $houseService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.houses.index');
    }

    public function read()
    {
        return $this->houseService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->houseService->create($request['code'], $request['name'], $request['status']);

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
        $result = $this->houseService->update(
            $id,
            $request['code'],
            $request['name'],
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
        }
    }
    public function delete($id)
    {
        $this->houseService->delete($id);

        return response()->json();
    }
}
