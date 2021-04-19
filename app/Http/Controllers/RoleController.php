<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\RoleService;

class RoleController extends Controller
{
    private $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->middleware('auth');
        $this->roleService = $roleService;
    }

    public function index()
    {
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
        $request->validate([
            'name' => 'required|max:255',
            'display_name' => 'required|max:255'
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => $request['permissions'][$i]
            ));
        }

        $result = $this->roleService->create(
            $request['name'],
            $request['display_name'],
            $request['description'],
            $rolePermissions
        );

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json();
        }
    }

    public function update($id, Request $request)
    {
        $rolePermissions = [];
        $inputtedRolePermissions = [];
        for ($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array(
                'id' => $request['permissions'][$i]
            ));
            array_push($inputtedRolePermissions, $request['permissions'][$i]);
        }

        $result = $this->roleService->update(
            $id,
            $request['name'],
            $request['display_name'],
            $request['description'],
            $rolePermissions,
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        /*
        $this->roleService->delete($id);
        */

        return response()->json();
    }
}
