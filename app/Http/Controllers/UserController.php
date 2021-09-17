<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\RoleService;
use App\Services\ActivityLogService;

use App\Rules\validDropDownValue;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

use Vinkla\Hashids\Facades\Hashids;

class UserController extends BaseController
{
    private $userService;
    private $roleService;
    private $activityLogService;

    public function __construct(UserService $userService, RoleService $roleService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

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
            'name' => 'required|alpha',
            'email' => 'required|email|max:255|unique:users',
            'roles' => 'required',
            'tax_id' => 'required',
            'ic_num' => 'required',
        ]);

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
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

        $rolesId = [];
        foreach ($request['roles'] as $r) {
            array_push($rolesId, Hashids::decode($r)[0]);
        }

        $result = $this->userService->create(
            $request['name'],
            $request['email'],
            $request['password'],
            $rolesId,
            $profile
        );

        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|alpha',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'roles' => 'required',
            'tax_id' => 'required',
            'ic_num' => 'required',
            'status' => new validDropDownValue('ACTIVE_STATUS'),
        ]);

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
        );

        $rolesId = [];
        foreach ($request['roles'] as $r) {
            array_push($rolesId, Hashids::decode($r)[0]);
        }

        $settings = [
            'THEME.CODEBASE' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['dateFormat'],
            'PREFS.TIME_FORMAT' => $request['timeFormat'],
        ];

        if ($request->hasFile('img_path')) {
            $image = $request->file('img_path');
            $filename = time().".".$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        }

        $result = $this->userService->update(
            $id,
            $request['name'],
            $rolesId,
            $profile,
            $settings
        );

        if ($request->filled('apiToken'))
            $this->userService->resetTokens($id);

        return $result == 0 ? response()->error():response()->success();
    }

    public function resetPassword($id)
    {
        $usr = $this->userService->getUserById($id);

        $this->userService->resetPassword($usr['email']);
    }

    public function sendEmailVerification()
    {
        $usr = Auth::user();

        $usr->sendEmailVerificationNotification();

        Session::flash('flash-messages', trans('user.flash.email_verification_sent'));

        return redirect()->back();
    }

    public function emailVerification(Request $request, $id, $hash)
    {
        if (!URL::hasValidSignature($request)) {
            return redirect('/');
        }

        $usr = $this->userService->getUserById($id);

        $usr->markEmailAsVerified();

        return redirect('/home');
    }

    public function setRoleByUserId(Request $request, $system)
    {
        $usr = Auth::user();

        switch($system) {
            case 'POS':
                $role = $this->roleService->getRoleByName('pos-owner');
                break;
            default:
                break;
        }

        if ($role)
            $this->userService->setRoleForUserId($usr->id, $role->id);

        return redirect('/logout');
    }
}
