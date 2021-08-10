<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\FinanceCashService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class FinanceCashController extends Controller
{
    private $financeCashService;
    private $activityLogService;

    public function __construct(FinanceCashService $financeCashService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->financeCashService = $financeCashService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());
        
        return view('finance.cashes.index');
    }

    public function read()
    {
        return $this->financeCashService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'status' => 'required'
        ]);
        
        $is_bank = $request['is_bank'];

        if ($is_bank == 'on') {
            $is_bank = 1;
        } else { 
            $is_bank = 0;
        }
        
        $result = $this->financeCashService->create($request['code'], $request['name'], $is_bank, $request['status']);
        
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
        $inputtedRolePermissions = [];
        for ($i = 0; $i < count($request['permissions']); $i++) {
            array_push($inputtedRolePermissions, array(
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->financeCashService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['is_bank'],
            $request['status'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $result = $this->financecashService->delete($id);

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
