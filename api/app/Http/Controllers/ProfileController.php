<?php

namespace App\Http\Controllers;

use App\Actions\Role\RoleActions;
use App\Actions\User\UserActions;
use App\Http\Requests\ProfileRequest;
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

    public function updateUser(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $userArr = [
            'name' => $request['name'],
        ];

        $result = $this->userActions->updateUser($user, $userArr, true);

        return ! $result ? response()->error() : response()->success();
    }

    public function updateProfile(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $profile = [
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

        $result = $this->userActions->updateProfile($user, $profile, true);

        return ! $result ? response()->error() : response()->success();
    }

    public function changePassword(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();

        $this->userActions->changePassword($usr, $request['new_password']);
    }

    public function updateSettings(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();
        $settings = [
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['date_format'],
            'PREFS.TIME_FORMAT' => $request['time_format'],
        ];

        $result = $this->userActions->updateSettings($usr, $settings, true);

        if (array_key_exists('api_token', $request)) {
            $this->userActions->resetTokens($usr->id);
        }

        return ! $result ? response()->error() : response()->success();
    }

    public function updateRoles(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();

        $roles = $request['roles'];
        $rolesId = $usr->roles->pluck('id');

        if ($roles === 'pos') {
            $rolesId->push($this->roleActions->readBy('NAME', 'POS-owner')->id);
        }

        $result = $this->userActions->updateRoles($usr, $rolesId->toArray(), true);

        return is_null($result) ? response()->error() : response()->success();
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
