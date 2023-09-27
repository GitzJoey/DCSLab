<?php

namespace App\Http\Controllers;

use App\Actions\Role\RoleActions;
use App\Actions\User\UserActions;
use App\Http\Requests\UserProfileRequest;
use App\Http\Resources\UserProfileResource;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    private $userActions;

    private $roleActions;

    public function __construct(UserActions $userActions, RoleActions $roleActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userActions = $userActions;
        $this->roleActions = $roleActions;
    }

    public function readProfile()
    {
        $result = $this->userActions->read(Auth::user());

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = (new UserProfileResource($result));

            return $response;
        }
    }

    public function updateUserProfile(UserProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $userArr = [
            'name' => $request['name'],
        ];

        $result = $this->userActions->updateUser($user, $userArr, true);

        return ! $result ? response()->error() : response()->success();
    }

    public function updatePersonalInformation(UserProfileRequest $profileRequest)
    {
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

        return ! $result ? response()->error() : response()->success();
    }

    public function updateAccountSettings(UserProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $settingsArr = [
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['date_format'],
            'PREFS.TIME_FORMAT' => $request['time_format'],
        ];

        $result = $this->userActions->updateSettings($user, $settingsArr, true);

        return ! $result ? response()->error() : response()->success();
    }

    public function updateUserRoles(UserProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $rolesArr = [];

        $result = $this->userActions->updateRoles($user, $rolesArr, true);

        return ! $result ? response()->error() : response()->success();
    }

    public function updatePassword(UserProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $new_pass = $request['password'];

        $result = $this->userActions->changePassword($user, $new_pass);

        return ! $result ? response()->error() : response()->success();
    }

    public function updateTokens(UserProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $result = $this->userActions->resetTokens($user);

        return ! $result ? response()->error() : response()->success();
    }

    public function checkTwoFAStatus()
    {
        return response()->json([
            'enabled' => auth()->user()->two_factor_secret !== null,
        ]);
    }

    public function sendEmailVerification()
    {
        /** @var \App\User */
        $usr = Auth::user();

        $usr->sendEmailVerificationNotification();

        return response()->success();
    }
}
