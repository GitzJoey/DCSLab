<?php

namespace App\Http\Controllers;

use App\Actions\Role\RoleActions;
use App\Actions\User\UserActions;
use App\Enums\UserRole;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    private UserActions $userActions;

    private RoleActions $roleActions;

    public function __construct(UserActions $userActions, RoleActions $roleActions)
    {
        parent::__construct();

        $this->userActions = $userActions;
        $this->roleActions = $roleActions;
    }

    public function show(): JsonResponse
    {
        $errorMsg = '';

        $result = $this->userActions->read(Auth::user());

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $response = (new UserProfileResource($result));

            return $this->apiResponse($response, Response::HTTP_OK);
        }
    }

    public function updateUserProfile(ProfileRequest $profileRequest): JsonResponse
    {
        $errorMsg = '';

        $request = $profileRequest->validated();
        $user = Auth::user();

        $userArr = [
            'name' => $request['name'],
        ];

        $result = $this->userActions->updateUser($user, $userArr, true);

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse($result, Response::HTTP_OK);
    }

    public function updatePersonalInformation(ProfileRequest $profileRequest): JsonResponse
    {
        $errorMsg = '';

        $request = $profileRequest->validated();
        $user = Auth::user();

        $profileArr = [
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        ];

        $result = $this->userActions->updateProfile($user, $profileArr, true);

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse($result, Response::HTTP_OK);
    }

    public function updateAccountSettings(ProfileRequest $profileRequest): JsonResponse
    {
        $errorMsg = '';

        $request = $profileRequest->validated();
        $user = Auth::user();

        $settingsArr = [
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['date_format'],
            'PREFS.TIME_FORMAT' => $request['time_format'],
        ];

        $result = $this->userActions->updateSettings($user, $settingsArr, true);

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse($result, Response::HTTP_OK);
    }

    public function updateUserRoles(ProfileRequest $profileRequest): JsonResponse
    {
        $errorMsg = '';

        $request = $profileRequest->validated();
        $user = Auth::user();

        $currentRole = $user->roles->pluck('id')->toArray();
        $addedRole = $request['roles'];

        $roleId = '';
        if ($addedRole == 'pos') {
            $roleId = $this->roleActions->readBy('NAME', UserRole::POS_OWNER->value)->id;
        }

        if (empty($roleId)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $rolesArr = array_merge($currentRole, [$roleId]);

        $result = $this->userActions->updateRoles($user, $rolesArr, true);

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse(new UserProfileResource($result), Response::HTTP_OK);
    }

    public function updatePassword(ProfileRequest $profileRequest): JsonResponse
    {
        $errorMsg = '';

        $request = $profileRequest->validated();
        $user = Auth::user();

        $new_pass = $request['password'];

        $result = $this->userActions->changePassword($user, $new_pass);

        if (is_null($result)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse($result, Response::HTTP_OK);
    }

    public function updateTokens(ProfileRequest $profileRequest): JsonResponse
    {
        $errorMsg = '';
        $user = Auth::user();

        $request = $profileRequest->validated();

        try {
            $this->userActions->resetTokens($user);
        } catch (Exception $e) {
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (! empty($errorMsg)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse(new UserProfileResource($user), Response::HTTP_OK);
    }

    public function sendEmailVerification(): JsonResponse
    {
        /** @var User $usr */
        $usr = Auth::user();

        $usr->sendEmailVerificationNotification();

        return $this->apiResponse('true', Response::HTTP_OK);
    }
}
