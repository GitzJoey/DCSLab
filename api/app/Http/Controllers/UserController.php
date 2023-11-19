<?php

namespace App\Http\Controllers;

use App\Actions\Randomizer\RandomizerActions;
use App\Actions\User\UserActions;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Vinkla\Hashids\Facades\Hashids;

class UserController extends BaseController
{
    private $userActions;

    public function __construct(UserActions $userActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userActions = $userActions;
    }

    public function readAny(UserRequest $userRequest)
    {
        //Throw Error
        //throw New \Exception('Test Exception From Controller');

        //Throw Empty Response Error (HttpStatus 500)
        //return response()->error();

        //Custom Validation Error 1 Message (HttpStatus 422)
        //return response()->error('Custom Validation Error 1 Message', 422);

        //Custom Validation With Multiple Error (HttpStatus 422)
        //return response()->error(['search' => ['Custom Validation With Multiple Error 1'], 'search' => ['Custom Validation With Multiple Error 2']], 422);

        $request = $userRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userActions->readAny(
                $search,
                $paginate,
                $page,
                $perPage,
                useCache: $useCache
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = UserResource::collection($result);

            return $response;
        }
    }

    public function read(User $user, UserRequest $userRequest)
    {
        $request = $userRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userActions->read($user);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new UserResource($result);

            return $response;
        }
    }

    public function store(UserRequest $userRequest)
    {
        $request = $userRequest->validated();

        $request['password'] = (new RandomizerActions())->generateAlphaNumeric(10);

        $userArr = [
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => $request['password'],
        ];

        $profileArr = [
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
        ];

        if (array_key_exists('img_path', $request) && $request['img_path']) {
            $image = $request['img_path'];
            $filename = time().'.'.$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profileArr['img_path'] = $file;
        }

        $rolesArr = [];
        foreach ($request['roles'] as $r) {
            array_push($rolesArr, Hashids::decode($r)[0]);
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userActions->create(
                $userArr,
                $rolesArr,
                $profileArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(User $user, UserRequest $userRequest)
    {
        $request = $userRequest->validated();

        $userArr = [
            'name' => $request['name'],
        ];

        $profileArr = [
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
        ];

        $rolesArr = [];
        foreach ($request['roles'] as $r) {
            array_push($rolesArr, Hashids::decode($r)[0]);
        }

        $settingsArr = [
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['date_format'],
            'PREFS.TIME_FORMAT' => $request['time_format'],
        ];

        if (array_key_exists('img_path', $request) && $request['img_path']) {
            $image = $request['img_path'];
            $filename = time().'.'.$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profileArr['img_path'] = $file;
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userActions->update(
                $user,
                $userArr,
                $rolesArr,
                $profileArr,
                $settingsArr
            );

            if (array_key_exists('api_token', $request)) {
                $this->userActions->resetTokens($user);
            }

            if (array_key_exists('reset_password', $request)) {
                $this->userActions->resetPassword(($user));
            }

            if (array_key_exists('reset_2fa', $request)) {
                $this->userActions->resetTwoFactorAuth(($user));
            }
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function getTokensCount(User $user)
    {
        return $this->userActions->getTokensCount($user);
    }
}
