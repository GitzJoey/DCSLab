<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

use App\Services\UserService;
use App\Services\RoleService;

class DevController extends BaseController
{
    private $userService;
    private $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function test(Request $request)
    {
        $u = $this->userService->readBy('ID', 1);

        $resource = new UserResource($u);
        return $resource;
    }
}
