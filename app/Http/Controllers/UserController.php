<?php

namespace App\Http\Controllers;

use App\Actions\RandomGenerator;
use App\Http\Requests\UserRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Services\RoleService;

use Illuminate\Http\Request;

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

    public function read(UserRequest $userRequest)
    {
        $request = $userRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = $request->has('page') ? abs($request['page']) : 1;
        $perPage = $request->has('perPage') ? abs($request['perPage']) : 10;

        $result = $this->userService->read($search, $paginate, $page, $perPage);
        
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = UserResource::collection($result);
            return $response;    
        }
    }

    public function getAllRoles()
    {
        $excludeRole = [
        ];

        $roles = $this->roleService->read(exclude: $excludeRole);
        
        if (is_null($roles)) {
            return response()->error();
        } else {
            $response = RoleResource::collection($roles);
            return $response;    
        }
    }

    public function store(UserRequest $userRequest)
    {
        //Throw Error
        //throw New \Exception('Test Exception From Controller');
        
        //Throw Empty Response Error (HttpStatus 500)
        //return response()->error();

        //Custom Validation Error 1 Message (HttpStatus 422)
        //return response()->error('Custom Validation Error 1 Message', 422);

        //Custom Validation With Multiple Error (HttpStatus 422)
        //return response()->error(['name' => ['Custom Validation With Multiple Error'], 'email' => ['Custom Validation With Multiple Error']], 422);

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

        return is_null($result) ? response()->error() : response()->success();
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

        return is_null($result) ? response()->error() : response()->success();
    }

    public function resetPassword($id)
    {
        $usr = $this->userService->readBy('ID', $id);

        $this->userService->resetPassword($usr['email']);
    }
}
