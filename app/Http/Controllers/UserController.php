<?php

namespace App\Http\Controllers;

use App\Actions\RandomGenerator;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\Services\RoleService;

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

    public function __construct(UserService $userService, RoleService $roleService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function read(Request $request)
    {
        $parameters = $request->all();
        $parameters['readAll'] = Auth::id();

        return $this->userService->read($parameters);
    }

    public function getAllRoles()
    {
        $withDefaultRole = Auth::user()->hasRole([
            Config::get('const.DEFAULT.ROLE.ADMIN'),
            Config::get('const.DEFAULT.ROLE.DEV')
        ]);

        $parameters['withDefaultRole'] = $withDefaultRole;

        $roles = $this->roleService->read($parameters);
        return $roles;
    }

    public function store(UserRequest $userRequest)
    {
        //throw New \Exception('Test Exception From Controller');

        $request = $userRequest->validated();

        $request['password'] = (new RandomGenerator())->generateAlphaNumeric(10);

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

        if (array_key_exists('img_path', $request)) {
            $image = $request['img_path'];
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

        return $result == null ? response()->error():response()->success();
    }

    public function update($id, UserRequest $userRequest)
    {
        $request = $userRequest->validated();

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
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['dateFormat'],
            'PREFS.TIME_FORMAT' => $request['timeFormat'],
        ];

        if (array_key_exists('img_path', $request)) {
            $image = $request['img_path'];
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

        if (array_key_exists('apiToken', $request))
            $this->userService->resetTokens($id);

        return $result == null ? response()->error():response()->success();
    }

    public function resetPassword($id)
    {
        $parameters['readById'] = $id;

        $usr = $this->userService->read($parameters);

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

        $parameters['readById'] = $id;

        $usr = $this->userService->read($parameters);

        $usr->markEmailAsVerified();

        return redirect('/home');
    }
}
