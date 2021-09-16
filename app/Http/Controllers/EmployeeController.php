<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use App\Services\EmployeeService;

class EmployeeController extends Controller
{
    private $EmployeeService;
    private $activityLogService;

    public function __construct(EmployeeService $EmployeeService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->EmployeeService = $EmployeeService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.employees.index');
    }

    public function read()
    {
        return $this->EmployeeService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:255',
            'email' => 'required|max:255',
        ]);

        $result = $this->EmployeeService->create(
            $request['nama'], 
            $request['email']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'nama' => 'required|max:255',
            'email' => 'required|max:255',
        ]);
        
        $result = $this->EmployeeService->update(
            $id,
            $request['nama'], 
            $request['email']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->EmployeeService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}

