<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\CashService;
use Illuminate\Http\Request;

class CashController extends Controller
{
    private $CashService;
    private $activityLogService;

    public function __construct(CashService $CashService, ActivityLogService $activityLogService)
    {
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
        return $this->CashService->read();
    }

    public function getAllActiveCash()
    {
        return $this->CashService->getAllActiveCash();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255|unique:cashes',
            'name' => 'required|max:255',
            'status' => 'required'
        ]);
        
        $is_bank = $request['is_bank'];
        $is_bank == 'on' ? $is_bank = 1 : $is_bank = 0;

        $result = $this->CashService->create(
            $request['code'], 
            $request['name'], 
            $is_bank, 
            $request['status']
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

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode($id, 'cash'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $is_bank = $request['is_bank'];
        $is_bank == 'on' ? $is_bank = 1 : $is_bank = 0;

        $result = $this->CashService->update(
            $id,
            $request['code'],
            $request['name'],
            $is_bank,
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
        $result = $this->CashService->delete($id);

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
