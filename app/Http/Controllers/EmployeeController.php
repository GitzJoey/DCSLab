<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use App\Services\EmployeeService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeController extends BaseController
{
    private $employeeService;
    private $roleService;
    private $userService;
    private $activityLogService;

    public function __construct(EmployeeService $employeeService, RoleService $roleService, UserService $userService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->employeeService = $employeeService;
        $this->roleService = $roleService;
        $this->userService = $userService;
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
        return $this->employeeService->read($userId);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required',
            'name' => 'required|alpha',
            'email' => 'required|email|max:255|unique:users',
        ]);

        $rolesId = [];
        array_push($rolesId, $this->roleService->getRoleByName('user')->id);

        $first_name = '';
        $last_name = '';
        if ($request['name'] == trim($request['name']) && strpos($request['name'], ' ') !== false) {
            $pieces = explode(" ", $request['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $request['name'];
        }

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
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

        $user = $this->userService->create(
            $request['name'],
            $request['email'],
            '',
            $rolesId,
            $profile
        );
        $user_id = Hashids::decode($user)[0];

        $result = $this->employeeService->create(
            Hashids::decode($request['company_id'])[0],
            $user_id
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {

        $request->validate([
            'company_id' => 'required',
            'name' => 'required|alpha',
        ]);

        $rolesId = [];
        array_push($rolesId, $this->roleService->getRoleByName('user')->id);

        $first_name = '';
        $last_name = '';
        if ($request['name'] == trim($request['name']) && strpos($request['name'], ' ') !== false) {
            $pieces = explode(" ", $request['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $request['name'];
        }

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
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

        $user = $this->userService->update(
            $request['name'],
            $request['email'],
            '',
            $rolesId,
            $profile
        );
        $user_id = Hashids::decode($user)[0];

        $result = $this->employeeService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $user_id
        );
        return $result == 0 ? response()->error():response()->success();
    }
    public function delete($id)
    {
        $result = $this->EmployeeService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}