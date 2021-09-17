<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Services\ActivityLogService;

use App\Rules\unchangedRoleName;

use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class RoleController extends BaseController
{
    private $roleService;
    private $activityLogService;

    public function __construct(RoleService $roleService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->roleService = $roleService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('role.index');
    }

    public function read()
    {
        return $this->roleService->read();
    }

    public function getAllPermissions()
    {
        return $this->roleService->getAllPermissions();
    }

    public function store(Request $request)
    {
        //throw New \Exception('Test Exception From Controller');

        //return response()->json([
        //    'message' => 'Test error from JSON HTTP Status 500'
        //], 500);

        $request->validate([
            'name' => ['required', 'alpha', 'max:255', 'unique:App\Models\Role,name'],
            'display_name' => 'required|max:255',
            'permissions' => 'required',
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->roleService->create(
            $request['name'],
            $request['display_name'],
            $request['description'],
            $rolePermissions
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => ['required','alpha', 'max:255', new unchangedRoleName($id)],
            'display_name' => 'required|max:255',
            'permissions' => 'required',
        ]);

        $inputtedRolePermissions = [];
        for ($i = 0; $i < count($request['permissions']); $i++) {
            array_push($inputtedRolePermissions, array(
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->roleService->update(
            $id,
            $request['name'],
            $request['display_name'],
            $request['description'],
            $inputtedRolePermissions
        );

        return $result == 0 ? response()->error():response()->success();
    }
}
