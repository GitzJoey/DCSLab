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
}
