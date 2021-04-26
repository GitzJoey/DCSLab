<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UserService;
use App\Services\RoleService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;

class UserController extends Controller
{
    private $userService;
    private $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function index()
    {
        return view('user.index');
    }

    public function read()
    {
        return $this->userService->read();
    }

    public function profile($id)
    {
        $user = '';
        return view('user.profile', compact('user'));
    }

    public function getAllRoles()
    {
        $withDefaultRole = Auth::user()->hasRole([
            Config::get('const.DEFAULT.ROLE.ADMIN'),
            Config::get('const.DEFAULT.ROLE.DEV')
        ]);

        $roles = $this->roleService->readRoles($withDefaultRole);
        return $roles;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|max:255|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'company_name' => 'required|max:255',
            'roles' => 'required',
            'tax_id' => 'required',
            'ic_num' => 'required',
        ]);

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        );

        $setting = $this->userService->createDefaultSetting();

        foreach ($request['roles'] as $r) {
            array_push($rolesId, Hashids::decode($r)[0]);
        }

        $this->userService->create(
            $request['name'],
            $request['email'],
            $request['password'],
            $rolesId,
            $profile,
            $setting
        );
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|max:255|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'company_name' => 'required|max:255',
            'roles' => 'required',
            'tax_id' => 'required',
            'ic_num' => 'required',
        ]);

        $setting = [];

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'company_name' => $request['company_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        );

        foreach ($request['roles'] as $r) {
            array_push($rolesId, Hashids::decode($r)[0]);
        }

        $this->userService->update(
            $id,
            $request['name'],
            $request['email'],
            $request['password'],
            $rolesId,
            $profile,
            $setting
        );
    }
}
