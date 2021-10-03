<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use App\Services\EmployeeService;
use App\Services\RoleService;

use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeController extends BaseController
{
    private $EmployeeService;
    private $roleService;
    private $activityLogService;

    public function __construct(EmployeeService $EmployeeService, RoleService $roleService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->EmployeeService = $EmployeeService;
        $this->roleService = $roleService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('company.employees.index');
    }

    public function read()
    {
        $userId = Auth::user()->id;
        return $this->EmployeeService->read($userId);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
        ]);

        $user = array (
            'name' => $request['name'],
            'email' => $request['email'],
        );

        $profile = array (
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
        );

        if ($request->hasFile('img_path')) {
            $image = $request->file('img_path');
            $filename = time().".".$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        }

        $role_id = [];
        array_push($role_id, $this->roleService->getRoleByName('user')->id);

        $result = $this->EmployeeService->create(
            Hashids::decode($request['company_id'])[0],
            $request['name'], 
            $request['email'],
            $request['password'], 
            $request['address'],
            $request['city'], 
            $request['postal_code'],
            $request['country'], 
            $request['tax_id'],
            $request['ic_num'], 
            $request['img_path'],
            $request['status'], 
            $request['remarks'],
            $role_id
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'company_id' => 'required',
        ]);

        if ($request->hasFile('img_path')) {
            $image = $request->file('img_path');
            $filename = time().".".$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $request['img_path'] = $file;
        }

        $role_id = [];
        array_push($role_id, $this->roleService->getRoleByName('user')->id);

        $result = $this->EmployeeService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['name'], 
            $request['email'],
            $request['password'], 
            $request['address'],
            $request['city'], 
            $request['postal_code'],
            $request['country'], 
            $request['tax_id'],
            $request['ic_num'], 
            $request['img_path'],
            $request['status'], 
            $request['remarks'],
            $role_id
        );
        return $result == 0 ? response()->error():response()->success();
    }
    public function delete($id)
    {
        $result = $this->EmployeeService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}