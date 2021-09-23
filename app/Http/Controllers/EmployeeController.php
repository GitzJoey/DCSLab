<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use App\Services\EmployeeService;

use Illuminate\Support\Facades\Auth;

class EmployeeController extends BaseController
{
    private $EmployeeService;
    private $activityLogService;

    public function __construct(EmployeeService $EmployeeService, ActivityLogService $activityLogService)
    {
        parent::__construct();

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
        return $this->EmployeeService->readAll();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
        ]);

        $result = $this->EmployeeService->create(
            $request['name'], 
            $request['email']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
        ]);
        
        $result = $this->EmployeeService->update(
            $id,
            $request['name'], 
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