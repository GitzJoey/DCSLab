<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\CompanyService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class CompanyController extends Controller
{
    private $companyService;
    private $activityLogService;

    public function __construct(CompanyService $companyService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->companyService = $companyService;
        $this->activityLogService = $activityLogService;

    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.companies.index');
    }

    public function read()
    {
        return $this->companyService->read();
    }

    public function getAllActiveCompany()
    {
        return $this->companyService->getAllActiveCompany();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->companyService->create($request['code'], $request['name'],$request['status']);

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
            'code' => 'required|max:255' ,
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $result = $this->companyService->update(
            $id,
            $request['code'],
            $request['name'],
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

    public function delete($id)
    {
        $result = $this->companyService->delete($id);

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