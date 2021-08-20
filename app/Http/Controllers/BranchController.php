<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\BranchService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class BranchController extends Controller
{
    private $branchService;
    private $activityLogService;

    public function __construct(BranchService $branchService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->branchService = $branchService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.branches.index');
    }

    public function read()
    {
        return $this->branchService->read();
    }

    public function store(Request $request)
    {  
        $request->validate([
            'company_id' => 'required',
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:255',
            'contact' => 'required|max:255',
            'remarks' => 'required|max:255',
            'status' => 'required'
        ]);
        
        $result = $this->branchService->create(
            $request['company_id'], 
            $request['code'], 
            $request['name'], 
            $request['address'], 
            $request['city'], 
            $request['contact'], 
            $request['remarks'], 
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
            'company_id' => 'required|max:255' ,
            'code' => 'required|max:255' ,
            'name' => 'required|max:255',
            'address' => 'required|max:255' ,
            'city' => 'required|max:255',
            'contact' => 'required|max:255' ,
            'remarks' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->branchService->update(
            $id,
            $request['company_id'],
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
        }
    }

    public function delete($id)
    {
        $result = $this->branchService->delete($id);

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