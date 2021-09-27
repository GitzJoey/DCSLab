<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use App\Services\EmployeeService;

use Vinkla\Hashids\Facades\Hashids;

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
            'company_id' => 'required',
            'name' => 'required|max:255',
            'email' => 'required|email',
            'status' => 'required'
        ]);

        $result = $this->EmployeeService->create(
            Hashids::decode($request['company_id'])[0],
            $request['name'], 
            $request['email'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['remarks'],
            $request['status']
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required|max:255',
            'email' => 'required|email',
            'status' => 'required'
        ]);
        
        $result = $this->EmployeeService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['name'], 
            $request['email'],
            $request['address'],
            $request['city'],
            $request['contact'],
            $request['remarks'],
            $request['status']
        );
        return $result == 0 ? response()->error():response()->success();
    }
    public function delete($id)
    {
        $result = $this->EmployeeService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}