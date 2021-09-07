<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\CompanyService;

use Vinkla\Hashids\Facades\Hashids;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

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
        $userId = Auth::user()->id;
        return $this->companyService->read($userId);
    }

    public function getAllActiveCompany()
    {
        return $this->companyService->getAllActiveCompany();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'code' => new uniqueCode('create', '', 'companies'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $default = $request['default'];
        if ($default == 'on') {
            $userId = Auth::user()->id;
            $this->companyService->resetDefaultCompany($userId);
        };

        $default = $request['default'];
        $default == 'on' ? $default = 1 : $default = 0;

        $result = $this->companyService->create(
            $request['code'],
            $request['name'],
            $default,
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
            'code' => new uniqueCode('update', $id, 'companies'),
            'name' => 'required|max:255',
            'status' => 'required'
        ]);

        $default = $request['default'];
        if ($default == "on") {
            $userId = Auth::user()->id;
            $this->companyService->resetDefaultCompany($userId);
        };

        $default = $request['default'];
        $default == 'on' ? $default = 1 : $default = 0;

        $result = $this->companyService->update(
            $id,
            $request['code'],
            $request['name'],
            $default,
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