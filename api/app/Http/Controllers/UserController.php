<?php

namespace App\Http\Controllers;

use App\Actions\System\RandomizerActions;
use App\Actions\User\UserActions;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Vinkla\Hashids\Facades\Hashids;

class UserController extends BaseController
{
    private UserActions $userActions;

    public function __construct(UserActions $userActions)
    {
        parent::__construct();

        $this->userActions = $userActions;
    }

    public function index(UserRequest $userRequest): JsonResponse
    {
        // Throw Error
        // throw New \Exception('Test Exception From Controller');

        // Throw Empty Response Error (HttpStatus 500)
        // return response()->error();

        // Custom Validation Error 1 Message (HttpStatus 422)
        // return response()->error('Custom Validation Error 1 Message', 422);

        // Custom Validation With Multiple Error (HttpStatus 422)
        // return response()->error(['search' => ['Custom Validation With Multiple Error 1'], 'search' => ['Custom Validation With Multiple Error 2']], 422);

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
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $response = UserResource::collection($result);

            return $this->apiResponse($response, Response::HTTP_OK);
        }
    }

    public function view(User $user, UserRequest $userRequest): JsonResponse
    {
        $request = $userRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userActions->read($user);
        } catch (Exception $e) {
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $response = new UserResource($result);

            return $this->apiResponse($response, Response::HTTP_OK);
        }
    }

    public function create(UserRequest $userRequest): JsonResponse
    {
        $request = $userRequest->validated();

        $request['password'] = (new RandomizerActions)->generateAlphaNumeric(10);

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
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse(new UserResource($result), Response::HTTP_CREATED);
    }

    public function update(User $user, UserRequest $userRequest): JsonResponse
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

            if (array_key_exists('api_token', $request) && (bool) $request['api_token']) {

                $this->userActions->resetTokens($user);
            }

            if (array_key_exists('reset_password', $request) && (bool) $request['reset_password']) {
                $this->userActions->resetPassword(($user));
            }

            if (array_key_exists('reset_2fa', $request) && (bool) $request['reset_2fa']) {
                $this->userActions->resetTwoFactorAuth(($user));
            }
        } catch (Exception $e) {
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse(new UserResource($result), Response::HTTP_OK);
    }

    public function getTokensCount(User $user): JsonResponse
    {
        return $this->apiResponse($this->userActions->getTokensCount($user), Response::HTTP_OK);
    }
}
