<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UserService;
use App\Services\RoleService;

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
        $roles = $this->roleService->readRoles(false);
        return $roles;
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'name' => 'required',
            'roles' => 'required',
            'tax_id' => 'required',
            'ic_num' => 'required',
        ]);

        $name = trim($request['first_name'] . ' ' . $request['last_name'], " ");
        $profile = [];
        $pic_phone = [];

        for ($j = 0; $j < count($request['phone_provider_id']); $j++) {
            array_push($pic_phone, array(
                'phone_provider_id' => Hashids::decode($request['phone_provider_id'][$j])[0],
                'number' => $request['phone_number'][$j],
                'remarks' => $request['remarks'][$j]
            ));
        }

        array_push($profile, array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'ic_num' => $request['ic_num'],
            'phone_numbers' => $pic_phone
        ));

        $rolesId = Hashids::decode($request['roles'])[0];

        $this->userService->create(
            $name,
            $request['email'],
            $request['password'],
            $rolesId,
            Auth::user()->company->id,
            $request['active'],
            $profile
        );
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'first_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'roles' => 'required',
            'company' => 'required',
        ]);

        $name = trim($request['first_name'] . ' ' . $request['last_name'], " ");
        $profile = [];
        $pic_phone = [];

        for ($j = 0; $j < count($request['phone_provider_id']); $j++) {
            array_push($pic_phone, array(
                'phone_provider_id' => Hashids::decode($request['phone_provider_id'][$j])[0],
                'number' => $request['phone_number'][$j],
                'remarks' => $request['remarks'][$j]
            ));
        }

        array_push($profile, array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'ic_num' => $request['ic_num'],
            'phone_numbers' => $pic_phone
        ));

        $rolesId = Hashids::decode($request['roles'])[0];

        $this->userService->update(
            $id,
            $name,
            $request['email'],
            $request['password'],
            $rolesId,
            $request['active'],
            Auth::user()->company->id,
            $profile
        );
    }
}
